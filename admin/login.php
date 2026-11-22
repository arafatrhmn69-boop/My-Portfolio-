<?php

declare(strict_types=1);

/**
 * Administrator sign-in.
 *
 * - CSRF-protected form
 * - password_verify() through Auth::attempt()
 * - database-backed lockout after LOGIN_MAX_ATTEMPTS failures
 * - generic error text (no username enumeration)
 * - redirects back to the originally requested admin page
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

if (Auth::check()) {
    redirect(admin_url('dashboard.php'));
}

$adminCount = Seeder::adminCount(Database::connection());

$username = '';
$error = '';

if (is_post()) {
    Csrf::requireValid();
    $username = input_string($_POST, 'username');

    if ($adminCount === 0) {
        $error = 'No administrator account exists yet. Run the installer first.';
    } else {
        $result = Auth::attempt($username, (string) ($_POST['password'] ?? ''));

        if ($result['status'] === 'ok') {
            $signedIn = Auth::user();
            Flash::add('success', 'Welcome back, ' . ($signedIn['username'] ?? $username) . '.');
            redirect(Auth::pullIntendedUrl() ?? admin_url('dashboard.php'));
        }

        if ($result['status'] === 'throttled') {
            $minutes = max(1, (int) ceil($result['retry_after'] / 60));
            $error = 'Too many failed sign-in attempts. Please wait about '
                . $minutes . ' minute' . ($minutes === 1 ? '' : 's') . ' and try again.';
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

$flashMessages = Flash::pull();
$heroImage = image_url('images/profile/hero-bg.jpg');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#07090a">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · <?= e(APP_NAME) ?> Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Manrope:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('admin/assets/admin.css')) ?>">
    <link rel="icon" href="<?= e(url('assets/trip-placeholder.svg')) ?>" type="image/svg+xml">
</head>

<body class="admin-body login-body">
    <a class="skip-link" href="#login-form">Skip to the sign-in form</a>

    <main class="login-shell">
        <section class="login-visual" aria-label="About this archive">
            <div class="login-visual__image" aria-hidden="true"
                style="background-image: url('<?= e($heroImage) ?>')"></div>

            <div class="login-brand">
                <span class="sidebar-mark" aria-hidden="true">JA</span>
                <span>
                    <span class="login-brand-title">THE JOURNEY ARCHIVE</span>
                    <span class="login-brand-sub">Admin console</span>
                </span>
            </div>

            <div class="login-quote">
                <p>“Every place has a story.<br>Every journey becomes<br>a memory.”</p>
                <footer>A personal travel archive · Est. 2024</footer>
            </div>
        </section>

        <section class="login-panel">
            <div class="login-form-wrap">
                <p class="login-eyebrow">Restricted access</p>
                <h1 class="login-title">Sign in to the <em>Archive</em></h1>
                <p class="login-sub">This area manages journeys and visitor messages. Only the site owner can
                    continue.</p>

                <?php if ($adminCount === 0): ?>
                    <div class="setup-note" role="alert">
                        <strong>No administrator account found.</strong><br>
                        Run the one-time installer from the project root, then reload this page:
                        <br><code>php database/setup.php</code>
                    </div>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <div class="admin-alert admin-alert--error" role="alert" style="margin-bottom:20px">
                        <i class="bi bi-exclamation-octagon" aria-hidden="true"></i>
                        <p><?= e($error) ?></p>
                    </div>
                <?php endif; ?>

                <?php foreach ($flashMessages as $flash): ?>
                    <?php $flashType = in_array($flash['type'], ['success', 'error', 'warning', 'info'], true) ? $flash['type'] : 'info'; ?>
                    <div class="admin-alert admin-alert--<?= e($flashType) ?>" role="status" style="margin-bottom:20px">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        <p><?= e($flash['message']) ?></p>
                    </div>
                <?php endforeach; ?>

                <form class="login-form" method="post" action="<?= e(admin_url('login.php')) ?>"
                    id="login-form" data-submit-once>
                    <?= Csrf::field() ?>

                    <div class="form-field">
                        <label class="form-label" for="username">Username</label>
                        <input class="form-control-admin" type="text" id="username" name="username"
                            value="<?= e($username) ?>" maxlength="190" autocomplete="username"
                            placeholder="Your admin username" required autofocus>
                    </div>

                    <div class="form-field">
                        <label class="form-label" for="password">Password</label>
                        <div class="password-wrap">
                            <input class="form-control-admin" type="password" id="password" name="password"
                                maxlength="200" autocomplete="current-password" placeholder="Your password" required>
                            <button class="password-toggle" type="button" data-toggle-password="password"
                                aria-label="Show password" aria-pressed="false">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <button class="login-submit" type="submit"<?= $adminCount === 0 ? ' disabled' : '' ?>>
                        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                        Enter the console
                    </button>
                </form>

                <div class="login-help">
                    <a href="<?= e(url()) ?>">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to the public site
                    </a>
                    <span>Signed-in sessions expire automatically.</span>
                </div>

                <p class="login-foot">
                    Protected by bcrypt password hashing, CSRF tokens, login throttling and hardened session
                    cookies. If you have lost your password, run
                    <code>php database/setup.php --new-password</code> on the server.
                </p>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="<?= e(url('admin/assets/admin.js')) ?>" defer></script>
</body>

</html>
