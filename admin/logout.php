<?php

declare(strict_types=1);

/**
 * Sign out. POST-only, CSRF-protected so another site cannot log the
 * administrator out with a forged image/link request.
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

if (!is_post()) {
    if (!headers_sent()) {
        header('Allow: POST');
    }
    abort_request(405, 'Method not allowed', 'Signing out requires a POST request from the admin console.');
}

Csrf::requireValid();
Auth::logout();
Csrf::rotate();
Flash::add('success', 'You have been signed out safely.');

redirect(admin_url('login.php'));
