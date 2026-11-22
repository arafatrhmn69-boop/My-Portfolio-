<?php

declare(strict_types=1);

/**
 * Admin layout — header, sidebar, top bar, page heading and flash messages.
 *
 * Controllers set any of these before including this file:
 *   $pageTitle     Browser tab title (plain text)
 *   $pageHeading   Large page heading (plain text)
 *   $pageEyebrow   Small label above the heading
 *   $pageSubtitle  Supporting sentence under the heading
 *   $pageActions   Trusted HTML for the header buttons
 *   $activePage    dashboard | trips | inquiries | account
 */

if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

$currentUser = Auth::user();

if ($currentUser === null) {
    redirect(admin_url('login.php'));
}

$pageTitle = $pageTitle ?? 'Dashboard';
$pageHeading = $pageHeading ?? $pageTitle;
$pageEyebrow = $pageEyebrow ?? 'Admin console';
$pageSubtitle = $pageSubtitle ?? '';
$pageActions = $pageActions ?? '';
$activePage = $activePage ?? '';

$tripModel = new Trip(Database::connection());
$inquiryModel = new Inquiry(Database::connection());

$navItems = [
    [
        'key'   => 'dashboard',
        'label' => 'Dashboard',
        'href'  => admin_url('dashboard.php'),
        'icon'  => 'bi-grid-1x2',
        'count' => null,
    ],
    [
        'key'   => 'trips',
        'label' => 'Trips & Destinations',
        'href'  => admin_url('trips/index.php'),
        'icon'  => 'bi-map',
        'count' => $tripModel->count(),
    ],
    [
        'key'   => 'inquiries',
        'label' => 'Inquiries',
        'href'  => admin_url('inquiries/index.php'),
        'icon'  => 'bi-envelope-paper',
        'count' => $inquiryModel->count(),
    ],
    [
        'key'   => 'account',
        'label' => 'Account & Security',
        'href'  => admin_url('account.php'),
        'icon'  => 'bi-shield-lock',
        'count' => null,
    ],
];

$flashMessages = Flash::pull();
$initial = mb_strtoupper(mb_substr((string) $currentUser['username'], 0, 1, 'UTF-8'), 'UTF-8');
$usesDefaultPassword = Auth::usingDefaultPassword();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#07090a">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?> · <?= e(APP_NAME) ?> Admin</title>

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

<body class="admin-body">
    <a class="skip-link" href="#admin-main">Skip to content</a>

    <div class="admin-shell">
        <aside class="admin-sidebar" id="adminSidebar" aria-label="Admin navigation">
            <div class="sidebar-inner">
                <a class="sidebar-brand" href="<?= e(admin_url('dashboard.php')) ?>">
                    <span class="sidebar-mark" aria-hidden="true">JA</span>
                    <span class="sidebar-brand-text">
                        <span class="sidebar-brand-title">THE JOURNEY<br>ARCHIVE</span>
                        <span class="sidebar-brand-sub">Admin console</span>
                    </span>
                </a>

                <p class="sidebar-label">Workspace</p>
                <nav class="admin-nav" aria-label="Main admin sections">
                    <?php foreach ($navItems as $item): ?>
                        <?php $isActive = $activePage === $item['key']; ?>
                        <a class="admin-nav__link<?= $isActive ? ' is-active' : '' ?>"
                            href="<?= e($item['href']) ?>"<?= $isActive ? ' aria-current="page"' : '' ?>>
                            <i class="bi <?= e($item['icon']) ?>" aria-hidden="true"></i>
                            <span><?= e($item['label']) ?></span>
                            <?php if ($item['count'] !== null): ?>
                                <span class="admin-nav__badge"><?= e((string) $item['count']) ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="sidebar-foot">
                    <p class="sidebar-note">
                        Signed in as <strong><?= e((string) $currentUser['username']) ?></strong>.
                        <?php if ($usesDefaultPassword): ?>
                            The seeded password is still active — change it in Account &amp; Security.
                        <?php else: ?>
                            Every action is protected by a session and a CSRF token.
                        <?php endif; ?>
                    </p>

                    <a class="btn-line w-100 mb-2" href="<?= e(url()) ?>" target="_blank" rel="noopener">
                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> View public site
                    </a>

                    <form class="sidebar-signout-form" method="post" action="<?= e(admin_url('logout.php')) ?>">
                        <?= Csrf::field() ?>
                        <button class="sidebar-signout" type="submit">
                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Sign out
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <button class="admin-backdrop" type="button" data-sidebar-backdrop aria-label="Close navigation" hidden></button>

        <div class="admin-main">
            <header class="admin-topbar">
                <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar"
                    aria-expanded="false" aria-label="Open navigation">
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>

                <p class="topbar-crumb">
                    <i class="bi bi-command" aria-hidden="true"></i>
                    Console
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    <strong><?= e($pageHeading) ?></strong>
                </p>

                <div class="topbar-actions">
                    <a class="topbar-link" href="<?= e(url()) ?>" target="_blank" rel="noopener">
                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                        <span>View site</span>
                    </a>

                    <div class="dropdown admin-user">
                        <button class="admin-user__toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <span class="admin-user__avatar" aria-hidden="true"><?= e($initial) ?></span>
                            <span class="admin-user__name"><?= e((string) $currentUser['username']) ?></span>
                            <i class="bi bi-chevron-down admin-user__caret" aria-hidden="true"></i>
                            <span class="sr-only">Account menu</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end admin-dropdown">
                            <li>
                                <h2 class="dropdown-header">Signed in as <?= e((string) $currentUser['username']) ?></h2>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= e(admin_url('account.php')) ?>">
                                    <i class="bi bi-shield-lock" aria-hidden="true"></i> Account &amp; security
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= e(url()) ?>" target="_blank" rel="noopener">
                                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> View public site
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" action="<?= e(admin_url('logout.php')) ?>">
                                    <?= Csrf::field() ?>
                                    <button class="dropdown-item" type="submit">
                                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Sign out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <main class="admin-content" id="admin-main" tabindex="-1">
                <?php if ($flashMessages !== []): ?>
                    <div class="admin-alerts">
                        <?php
                        $flashIcons = [
                            'success' => 'bi-check-circle',
                            'error'   => 'bi-exclamation-octagon',
                            'warning' => 'bi-exclamation-triangle',
                            'info'    => 'bi-info-circle',
                        ];
                        ?>
                        <?php foreach ($flashMessages as $flash): ?>
                            <?php
                            $flashType = in_array($flash['type'], ['success', 'error', 'warning', 'info'], true)
                                ? $flash['type']
                                : 'info';
                            $flashIcon = $flashIcons[$flashType];
                            ?>
                            <div class="admin-alert admin-alert--<?= e($flashType) ?>" role="status" data-flash>
                                <i class="bi <?= e($flashIcon) ?>" aria-hidden="true"></i>
                                <p><?= e($flash['message']) ?></p>
                                <button class="alert-dismiss" type="button" data-dismiss-flash
                                    aria-label="Dismiss message">
                                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($usesDefaultPassword && $activePage === 'dashboard'): ?>
                    <div class="admin-alert admin-alert--warning" role="alert" style="margin-bottom:24px">
                        <i class="bi bi-shield-exclamation" aria-hidden="true"></i>
                        <p>
                            You are still using the password created by the installer.
                            <a href="<?= e(admin_url('account.php')) ?>">Change it now</a> before sharing this site.
                        </p>
                    </div>
                <?php endif; ?>

                <div class="page-head">
                    <div>
                        <p class="page-eyebrow"><?= e($pageEyebrow) ?></p>
                        <h1 class="page-title"><?= e($pageHeading) ?></h1>
                        <?php if ($pageSubtitle !== ''): ?>
                            <p class="page-subtitle"><?= e($pageSubtitle) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ($pageActions !== ''): ?>
                        <div class="page-actions"><?= $pageActions ?></div>
                    <?php endif; ?>
                </div>
