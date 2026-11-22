<?php

declare(strict_types=1);

/**
 * /admin/ entry point — sends visitors to the dashboard (or the login page
 * when they are not signed in).
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

Auth::requireLogin();
redirect(admin_url('dashboard.php'));
