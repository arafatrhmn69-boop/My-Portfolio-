<?php

declare(strict_types=1);

/**
 * ============================================================
 *  THE JOURNEY ARCHIVE — one-time installer
 * ============================================================
 *  Creates database/portfolio.sqlite, all four tables, the default
 *  administrator and (optionally) seven starter trips.
 *
 *  Run from the project root:
 *      php database/setup.php
 *      php database/setup.php --no-demo
 *      php database/setup.php --new-password
 *      php database/setup.php --new-password --user=another_admin
 *
 *  Web access is allowed only while no administrator exists, so it
 *  can never reset a live installation. Delete this file (or keep the
 *  database/.htaccess in place) after installing.
 * ============================================================
 */

define('APP_NO_SESSION', true);

require_once dirname(__DIR__) . '/config/bootstrap.php';

$isCli = PHP_SAPI === 'cli';
$arguments = $isCli ? $argv : [];
$generatePassword = in_array('--new-password', $arguments, true);
$seedDemoTrips = !in_array('--no-demo', $arguments, true);

$requestedUser = DEFAULT_ADMIN_USERNAME;
foreach ($arguments as $argument) {
    if (str_starts_with($argument, '--user=')) {
        $requestedUser = substr($argument, strlen('--user='));
    }
}

$pdo = Database::connection();

// ---------------------------------------------------------------------------
//  Password reset mode (CLI only — web visitors must never trigger this)
// ---------------------------------------------------------------------------
if ($generatePassword) {
    if (!$isCli) {
        abort_request(403, 'CLI only', 'Password reset is only available from the command line.');
    }

    $statement = $pdo->prepare('SELECT id, username FROM admins WHERE username = :username COLLATE NOCASE LIMIT 1');
    $statement->execute([':username' => $requestedUser]);
    $admin = $statement->fetch();

    if (!is_array($admin)) {
        fwrite(STDERR, 'No administrator named "' . $requestedUser . '" exists.' . PHP_EOL);
        fwrite(STDERR, 'Run `php database/setup.php` first, or omit --user to use the seeded account.' . PHP_EOL);
        exit(1);
    }

    $newPassword = Seeder::randomPassword(18);
    $update = $pdo->prepare('UPDATE admins SET password_hash = :hash WHERE id = :id');
    $update->execute([
        ':hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ':id'   => $admin['id'],
    ]);

    // Old lockouts should not keep the owner out after a reset.
    $pdo->prepare('DELETE FROM login_attempts WHERE username = :username')
        ->execute([':username' => $admin['username']]);

    echo PHP_EOL;
    echo '  Password reset for: ' . $admin['username'] . PHP_EOL;
    echo '  New password:       ' . $newPassword . PHP_EOL;
    echo PHP_EOL;
    echo '  Store this password now — it is shown once and is not saved as plain text.' . PHP_EOL;
    echo '  Sign in, then change it from Account & Security.' . PHP_EOL . PHP_EOL;
    exit(0);
}

// ---------------------------------------------------------------------------
//  Web guard: never expose the installer on an installed site
// ---------------------------------------------------------------------------
$adminsBefore = Seeder::adminCount($pdo);

if (!$isCli && $adminsBefore > 0) {
    if (!headers_sent()) {
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!DOCTYPE html><html lang="en" data-bs-theme="dark"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Already installed</title>'
        . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">'
        . '<link rel="stylesheet" href="' . e(url('admin/assets/admin.css')) . '">'
        . '</head><body class="admin-body"><main class="container py-5" style="max-width:640px">'
        . '<p class="page-eyebrow">Installer locked</p>'
        . '<h1 class="page-title">Already installed</h1>'
        . '<p class="page-subtitle">This site already has an administrator account, so the installer will not '
        . 'run again. Sign in instead, or use the command line if you need to reset a password.</p>'
        . '<div class="page-actions" style="margin-top:24px">'
        . '<a class="btn-gold" href="' . e(admin_url('login.php')) . '">Go to sign in</a>'
        . '<a class="btn-line" href="' . e(url()) . '">Back to the site</a>'
        . '</div></body></html>';
    exit;
}

// ---------------------------------------------------------------------------
//  Install
// ---------------------------------------------------------------------------
$adminCreated = Seeder::seedAdmin($pdo);
$tripsCreated = $seedDemoTrips ? Seeder::seedTrips($pdo) : 0;

$tableCounts = [];
foreach (['admins', 'trips', 'inquiries', 'login_attempts'] as $table) {
    $tableCounts[$table] = (int) $pdo->query('SELECT COUNT(*) FROM ' . $table)->fetchColumn();
}

$loginUrl = $isCli
    ? 'Open /admin/login.php in your browser.'
    : admin_url('login.php');

// ---------------------------------------------------------------------------
//  Output
// ---------------------------------------------------------------------------
if ($isCli) {
    echo PHP_EOL;
    echo '  THE JOURNEY ARCHIVE — installer' . PHP_EOL;
    echo '  ================================' . PHP_EOL;
    echo '  Database:      database/portfolio.sqlite' . PHP_EOL;
    echo '  Tables ready:  ' . implode(', ', array_keys($tableCounts)) . PHP_EOL;
    echo '  Administrator: ' . DEFAULT_ADMIN_USERNAME
        . ($adminCreated ? ' (created now)' : ' (already existed)') . PHP_EOL;
    echo '  Demo trips:    ' . $tripsCreated . ' inserted' . PHP_EOL;
    echo PHP_EOL;
    echo '  Sign in:       ' . $loginUrl . PHP_EOL;
    echo '  IMPORTANT: use the password supplied with this project, then change it' . PHP_EOL;
    echo '             immediately under Account & Security.' . PHP_EOL;
    echo '  Lost password: php database/setup.php --new-password' . PHP_EOL;
    echo '  Security:      delete or block database/setup.php after installing.' . PHP_EOL;
    echo PHP_EOL;
    exit(0);
}

if (!headers_sent()) {
    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Installation complete · <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Manrope:wght@300;400;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('admin/assets/admin.css')) ?>">
</head>

<body class="admin-body">
    <main class="container py-5" style="max-width:760px">
        <p class="page-eyebrow">One-time installer</p>
        <h1 class="page-title">Installation <em>complete</em></h1>
        <p class="page-subtitle">The SQLite database, tables, administrator and starter journeys are ready.</p>

        <div class="admin-card" style="margin-top:26px">
            <div class="admin-card__head">
                <h2 class="admin-card__title">What was <span>created</span></h2>
                <span class="badge-soft badge-soft--gold">Success</span>
            </div>
            <div class="admin-card__body">
                <dl class="inquiry-meta">
                    <div>
                        <dt>Database</dt>
                        <dd><code>database/portfolio.sqlite</code></dd>
                    </div>
                    <div>
                        <dt>Tables</dt>
                        <dd>admins, trips, inquiries, login_attempts</dd>
                    </div>
                    <div>
                        <dt>Administrator</dt>
                        <dd><?= e(DEFAULT_ADMIN_USERNAME) ?> <?= $adminCreated ? '(created)' : '(existing)' ?></dd>
                    </div>
                    <div>
                        <dt>Demo trips inserted</dt>
                        <dd><?= e((string) $tripsCreated) ?></dd>
                    </div>
                </dl>

                <div class="admin-alert admin-alert--warning" role="alert" style="margin-top:22px">
                    <i class="bi bi-shield-exclamation" aria-hidden="true"></i>
                    <p>
                        Use the password supplied with the project, then change it immediately in
                        <strong>Account &amp; Security</strong>. No plain-text password is stored in this repository.
                    </p>
                </div>

                <div class="form-actions" style="margin-top:22px">
                    <a class="btn-gold" href="<?= e(admin_url('login.php')) ?>">
                        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Sign in to the console
                    </a>
                    <a class="btn-line" href="<?= e(url()) ?>">View the site</a>
                </div>
            </div>
        </div>

        <div class="admin-alert admin-alert--info" role="note" style="margin-top:18px">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            <p>
                <strong>Next step:</strong> delete <code>database/setup.php</code> from the server, or leave
                <code>database/.htaccess</code> in place. Lost the password later? Run
                <code>php database/setup.php --new-password</code> from the command line.
            </p>
        </div>

        <p class="form-hint" style="margin-top:22px">
            Never committed: the plain-text password, the database file and uploaded photographs are all
            excluded by <code>.gitignore</code>.
        </p>
    </main>
</body>

</html>
