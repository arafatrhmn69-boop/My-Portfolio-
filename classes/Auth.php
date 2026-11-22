<?php

declare(strict_types=1);

/**
 * Session-based administrator authentication.
 *
 * Security properties:
 *  - passwords are verified with password_verify() (bcrypt via PASSWORD_DEFAULT)
 *  - session ID is regenerated on login and on logout (fixation defence)
 *  - cookie is HttpOnly, SameSite=Strict and Secure over HTTPS
 *  - idle timeout + absolute timeout
 *  - user-agent fingerprint check
 *  - database-backed failed-login throttling
 *  - username changes require the current password and an unused name
 *  - the account is re-checked against the database on every request
 */
final class Auth
{
    private static bool $resolved = false;
    private static ?array $user = null;

    private function __construct()
    {
    }

    /**
     * Attempt a login.
     *
     * @return array{status: 'ok'|'invalid'|'throttled', retry_after: int}
     *         retry_after is in seconds and only set for "throttled".
     */
    public static function attempt(string $username, string $password): array
    {
        $username = trim($username);

        if ($username === '' || $password === '') {
            return ['status' => 'invalid', 'retry_after' => 0];
        }

        $pdo = Database::connection();
        $ip = client_ip();
        $failureCount = self::failureCount($pdo, $username, $ip);

        if ($failureCount >= LOGIN_MAX_ATTEMPTS) {
            return ['status' => 'throttled', 'retry_after' => self::retryAfter($pdo, $username, $ip)];
        }

        $statement = $pdo->prepare(
            'SELECT id, username, password_hash, created_at
             FROM admins
             WHERE username = :username COLLATE NOCASE
             LIMIT 1'
        );
        $statement->execute([':username' => $username]);
        $admin = $statement->fetch();

        // Verify against a dummy digest when the username is unknown so the
        // response time does not reveal whether an account exists.
        $hash = is_array($admin) ? (string) $admin['password_hash'] : DUMMY_PASSWORD_HASH;
        $valid = password_verify($password, $hash);

        if (!is_array($admin) || !$valid) {
            self::recordFailure($pdo, $username, $ip);
            usleep(random_int(150000, 350000));

            return ['status' => 'invalid', 'retry_after' => 0];
        }

        // Transparently upgrade older/weaker hashes.
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $update = $pdo->prepare('UPDATE admins SET password_hash = :hash WHERE id = :id');
            $update->execute([
                ':hash' => password_hash($password, PASSWORD_DEFAULT),
                ':id'   => $admin['id'],
            ]);
        }

        self::clearFailures($pdo, $username, $ip);
        self::login($admin);

        return ['status' => 'ok', 'retry_after' => 0];
    }

    /**
     * Is there a valid, non-expired administrator session?
     */
    public static function check(): bool
    {
        if (self::$resolved) {
            return self::$user !== null;
        }

        self::$resolved = true;
        self::$user = null;

        if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['admin_id'])) {
            return false;
        }

        $now = time();
        $lastSeen = (int) ($_SESSION['admin_last_seen'] ?? 0);
        $loginTime = (int) ($_SESSION['admin_login_time'] ?? 0);

        if ($lastSeen <= 0 || $now - $lastSeen > SESSION_IDLE_TIMEOUT) {
            self::logout();

            return false;
        }

        if ($loginTime <= 0 || $now - $loginTime > SESSION_ABSOLUTE_TIMEOUT) {
            self::logout();

            return false;
        }

        $fingerprint = (string) ($_SESSION['admin_fingerprint'] ?? '');
        if ($fingerprint === '' || !hash_equals($fingerprint, self::fingerprint())) {
            self::logout();

            return false;
        }

        $statement = Database::connection()->prepare(
            'SELECT id, username, password_hash, created_at FROM admins WHERE id = :id LIMIT 1'
        );
        $statement->execute([':id' => (int) $_SESSION['admin_id']]);
        $admin = $statement->fetch();

        if (!is_array($admin)) {
            self::logout();

            return false;
        }

        $_SESSION['admin_last_seen'] = $now;
        self::$user = $admin;

        return true;
    }

    /**
     * Current administrator row, or null when signed out.
     */
    public static function user(): ?array
    {
        self::check();

        return self::$user;
    }

    /**
     * Redirect unauthenticated visitors to the login page, remembering the
     * page they originally requested (safe, local admin URLs only).
     */
    public static function requireLogin(): void
    {
        if (self::check()) {
            return;
        }

        if (!is_post() && !empty($_SERVER['REQUEST_URI'])) {
            $_SESSION['intended_url'] = (string) $_SERVER['REQUEST_URI'];
        }

        redirect(admin_url('login.php'));
    }

    /**
     * Validate and consume a stored intended URL after login.
     */
    public static function pullIntendedUrl(): ?string
    {
        $intended = $_SESSION['intended_url'] ?? null;
        unset($_SESSION['intended_url']);

        if (!is_string($intended) || $intended === '' || preg_match('/[\r\n]/', $intended) === 1) {
            return null;
        }

        $prefix = app_base_url() . '/admin/';

        return str_starts_with($intended, $prefix) ? $intended : null;
    }

    /**
     * Validate the format of a proposed administrator username.
     *
     * @return string|null Null when valid, otherwise a user-safe message.
     */
    public static function usernameValidationError(string $username): ?string
    {
        $username = trim($username);

        if ($username === '') {
            return 'Enter a new username.';
        }

        $length = mb_strlen($username, 'UTF-8');

        if ($length < ADMIN_USERNAME_MIN_LENGTH || $length > ADMIN_USERNAME_MAX_LENGTH) {
            return sprintf(
                'Use between %d and %d characters.',
                ADMIN_USERNAME_MIN_LENGTH,
                ADMIN_USERNAME_MAX_LENGTH
            );
        }

        if (preg_match('/^[A-Za-z][A-Za-z0-9._-]*$/', $username) !== 1) {
            return 'Start with a letter and use only letters, numbers, dots, underscores or hyphens.';
        }

        return null;
    }

    /**
     * Change the signed-in administrator's username.
     *
     * The current password is verified again, the name must be unused
     * (case-insensitively), and the session/CSRF state is refreshed.
     *
     * @return string Empty string on success, otherwise an error message.
     */
    public static function changeUsername(int $adminId, string $currentPassword, string $newUsername): string
    {
        $newUsername = trim($newUsername);
        $validationError = self::usernameValidationError($newUsername);

        if ($validationError !== null) {
            return $validationError;
        }

        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT username, password_hash FROM admins WHERE id = :id LIMIT 1'
        );
        $statement->execute([':id' => $adminId]);
        $admin = $statement->fetch();

        if (!is_array($admin)) {
            return 'The administrator account could not be found.';
        }

        if (!password_verify($currentPassword, (string) $admin['password_hash'])) {
            usleep(random_int(150000, 350000));

            return 'Your current password is not correct.';
        }

        if (strcasecmp($newUsername, (string) $admin['username']) === 0) {
            return 'That is already your username.';
        }

        $duplicate = $pdo->prepare(
            'SELECT id FROM admins
             WHERE username = :username COLLATE NOCASE AND id != :id
             LIMIT 1'
        );
        $duplicate->execute([
            ':username' => $newUsername,
            ':id'       => $adminId,
        ]);

        if ($duplicate->fetchColumn() !== false) {
            return 'That username is already in use.';
        }

        $update = $pdo->prepare('UPDATE admins SET username = :username WHERE id = :id');

        try {
            $update->execute([
                ':username' => $newUsername,
                ':id'       => $adminId,
            ]);
        } catch (PDOException $error) {
            if (str_contains(strtolower($error->getMessage()), 'unique')) {
                return 'That username is already in use.';
            }

            throw $error;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['admin_username'] = $newUsername;
        }

        // Force the next Auth::user() call to read the updated row, rotate the
        // CSRF token and roll the session ID after this sensitive change.
        self::$resolved = false;
        self::$user = null;
        Csrf::rotate();

        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }

        return '';
    }

    /**
     * Change the signed-in administrator's password.
     *
     * @return string Empty string on success, otherwise an error message.
     */
    public static function changePassword(int $adminId, string $current, string $new): string
    {
        if (strlen($new) < 12) {
            return 'The new password must be at least 12 characters long.';
        }

        if (!preg_match('/[A-Za-z]/', $new) || !preg_match('/[0-9]/', $new)) {
            return 'Use at least one letter and one number in the new password.';
        }

        $pdo = Database::connection();
        $statement = $pdo->prepare('SELECT password_hash FROM admins WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $adminId]);
        $hash = $statement->fetchColumn();

        if (!is_string($hash) || !password_verify($current, $hash)) {
            return 'Your current password is not correct.';
        }

        if (password_verify($new, $hash)) {
            return 'The new password must be different from the current one.';
        }

        $update = $pdo->prepare('UPDATE admins SET password_hash = :hash WHERE id = :id');
        $update->execute([
            ':hash' => password_hash($new, PASSWORD_DEFAULT),
            ':id'   => $adminId,
        ]);

        // Invalidate other sessions' CSRF tokens and roll the session ID.
        Csrf::rotate();
        self::$resolved = false;
        self::$user = null;
        session_regenerate_id(true);

        return '';
    }

    /**
     * True while the seeded default digest is still in use (dashboard warning).
     */
    public static function usingDefaultPassword(): bool
    {
        $user = self::user();

        return is_array($user)
            && isset($user['password_hash'])
            && hash_equals(DEFAULT_ADMIN_PASSWORD_HASH, (string) $user['password_hash']);
    }

    public static function logout(): void
    {
        self::$resolved = true;
        self::$user = null;

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];

            if (!headers_sent()) {
                session_regenerate_id(true);
            }
        }
    }

    private static function login(array $admin): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('A PHP session is required to sign in.');
        }

        session_regenerate_id(true);

        // Keep the page the visitor originally asked for across the reset.
        $intendedUrl = $_SESSION['intended_url'] ?? null;

        $_SESSION = [];

        if (is_string($intendedUrl) && $intendedUrl !== '') {
            $_SESSION['intended_url'] = $intendedUrl;
        }

        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_username'] = (string) $admin['username'];
        $_SESSION['admin_login_time'] = time();
        $_SESSION['admin_last_seen'] = time();
        $_SESSION['admin_fingerprint'] = self::fingerprint();

        self::$resolved = true;
        self::$user = [
            'id'            => (int) $admin['id'],
            'username'      => (string) $admin['username'],
            'password_hash' => (string) ($admin['password_hash'] ?? ''),
            'created_at'    => (string) ($admin['created_at'] ?? ''),
        ];
    }

    private static function fingerprint(): string
    {
        return hash('sha256', 'ja|' . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'));
    }

    private static function failureCount(PDO $pdo, string $username, string $ip): int
    {
        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE username = :username AND ip = :ip AND attempted_at >= :since'
        );
        $statement->execute([
            ':username' => mb_substr($username, 0, 190),
            ':ip'       => $ip,
            ':since'    => gmdate('Y-m-d H:i:s', time() - LOGIN_WINDOW),
        ]);

        return (int) $statement->fetchColumn();
    }

    private static function retryAfter(PDO $pdo, string $username, string $ip): int
    {
        $statement = $pdo->prepare(
            'SELECT MIN(attempted_at) FROM login_attempts
             WHERE username = :username AND ip = :ip AND attempted_at >= :since'
        );
        $statement->execute([
            ':username' => mb_substr($username, 0, 190),
            ':ip'       => $ip,
            ':since'    => gmdate('Y-m-d H:i:s', time() - LOGIN_WINDOW),
        ]);

        $oldest = $statement->fetchColumn();
        if (!is_string($oldest) || $oldest === '') {
            return LOGIN_WINDOW;
        }

        $elapsed = time() - (new DateTimeImmutable($oldest, new DateTimeZone('UTC')))->getTimestamp();
        $remaining = LOGIN_WINDOW - $elapsed;

        return max(1, min(LOGIN_WINDOW, $remaining));
    }

    private static function recordFailure(PDO $pdo, string $username, string $ip): void
    {
        // Keep the table small: attempts older than 24 hours are irrelevant.
        $pdo->prepare('DELETE FROM login_attempts WHERE attempted_at < :cutoff')
            ->execute([':cutoff' => gmdate('Y-m-d H:i:s', time() - 86400)]);

        $statement = $pdo->prepare(
            'INSERT INTO login_attempts (username, ip) VALUES (:username, :ip)'
        );
        $statement->execute([
            ':username' => mb_substr($username, 0, 190),
            ':ip'       => $ip,
        ]);
    }

    private static function clearFailures(PDO $pdo, string $username, string $ip): void
    {
        $statement = $pdo->prepare('DELETE FROM login_attempts WHERE username = :username AND ip = :ip');
        $statement->execute([
            ':username' => mb_substr($username, 0, 190),
            ':ip'       => $ip,
        ]);
    }
}
