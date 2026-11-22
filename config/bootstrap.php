<?php

declare(strict_types=1);

/**
 * ============================================================
 *  Bootstrap — required by every PHP entry point
 * ============================================================
 *  Loads configuration and helpers, registers the class
 *  autoloader, configures hardened sessions, sets security
 *  headers and installs a production-safe error handler.
 * ============================================================
 */

require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/includes/helpers.php';

spl_autoload_register(static function (string $class): void {
    $file = BASE_PATH . '/classes/' . $class . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

// ---------------------------------------------------------------------------
//  Errors: never show stack traces or paths on a live server.
// ---------------------------------------------------------------------------
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

set_exception_handler(static function (Throwable $error): void {
    error_log(sprintf(
        '[%s] %s: %s in %s:%d',
        APP_NAME,
        $error::class,
        $error->getMessage(),
        $error->getFile(),
        $error->getLine()
    ));

    $detail = APP_DEBUG
        ? $error->getMessage() . ' — ' . $error->getFile() . ':' . $error->getLine()
        : 'An unexpected server error occurred. Please try again in a moment.';

    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, 'ERROR: ' . $detail . PHP_EOL);
        exit(1);
    }

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }

    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Server error · ' . e(APP_NAME) . '</title>'
        . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">'
        . '</head><body class="bg-dark text-light d-flex align-items-center" style="min-height:100vh">'
        . '<main class="container py-5" style="max-width:640px">'
        . '<p class="text-uppercase small text-warning mb-2">Error 500</p>'
        . '<h1 class="h3 mb-3">Something went wrong</h1>'
        . '<p class="text-secondary">' . e($detail) . '</p>'
        . '<a class="btn btn-outline-warning mt-3" href="' . e(url()) . '">Back to the site</a>'
        . '</main></body></html>';
    exit;
});

// ---------------------------------------------------------------------------
//  Session hardening
//  Public read-only pages may define APP_NO_SESSION before requiring this
//  file to avoid setting a session cookie for anonymous visitors.
// ---------------------------------------------------------------------------
$sessionEnabled = !defined('APP_NO_SESSION') || APP_NO_SESSION !== true;

if ($sessionEnabled && PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443')
        || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');

    $cookiePath = app_base_url() === '' ? '/' : app_base_url() . '/';

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.gc_maxlifetime', (string) SESSION_ABSOLUTE_TIMEOUT);

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $cookiePath,
        'domain'   => '',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

// ---------------------------------------------------------------------------
//  Response headers
// ---------------------------------------------------------------------------
send_security_headers();

$scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');

if (str_contains($scriptName, '/admin/') && !headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}
