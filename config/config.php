<?php

declare(strict_types=1);

/**
 * ============================================================
 *  THE JOURNEY ARCHIVE — Admin configuration
 * ============================================================
 *  Every tunable value for the admin panel lives here. You should
 *  not need to edit any other file to change paths, limits or the
 *  default administrator account.
 *
 *  IMPORTANT: this file is never served directly. The .htaccess in
 *  /config denies HTTP access, and it contains no plain-text
 *  password — only a one-way password_hash() digest.
 * ============================================================
 */

// ---------------------------------------------------------------------------
//  Paths (absolute filesystem paths)
// ---------------------------------------------------------------------------
define('BASE_PATH', dirname(__DIR__));
define('DB_PATH', BASE_PATH . '/database/portfolio.sqlite');
define('UPLOAD_PATH', BASE_PATH . '/uploads/trips');

/** Public URL (relative to the site root) used for uploaded trip images. */
define('UPLOAD_URL_PREFIX', 'uploads/trips');

/** Fallback card image used when a trip has no photograph. */
define('PLACEHOLDER_IMAGE', 'assets/trip-placeholder.svg');

// ---------------------------------------------------------------------------
//  Application
// ---------------------------------------------------------------------------
define('APP_NAME', 'The Journey Archive');
define('APP_TIMEZONE', 'Asia/Dhaka');

/**
 * Debug mode. Keep this FALSE on a public server: error messages can
 * reveal file paths and database details. Set to true only locally.
 */
define('APP_DEBUG', false);

/**
 * Base URL auto-detection normally works (domain root, sub-folder, and
 * `php -S` are all supported). If your host uses an unusual document
 * root and links look wrong, uncomment and set this:
 *
 * define('APP_BASE_URL', '/my-portfolio');
 */

// ---------------------------------------------------------------------------
//  Sessions & authentication
// ---------------------------------------------------------------------------
define('SESSION_NAME', 'journey_admin');
define('SESSION_IDLE_TIMEOUT', 60 * 30);        // 30 minutes without activity
define('SESSION_ABSOLUTE_TIMEOUT', 60 * 60 * 2); // 2 hours total, then re-login

/** Failed logins allowed inside LOGIN_WINDOW before a temporary lockout. */
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_WINDOW', 60 * 15);                 // 15 minutes

// ---------------------------------------------------------------------------
//  Default administrator (used only once, by database/setup.php)
// ---------------------------------------------------------------------------
/**
 * The password below is stored as a bcrypt digest produced with
 * password_hash($plainText, PASSWORD_DEFAULT). The plain-text password is
 * NOT stored anywhere in this repository. After your first login, change
 * it from Admin → Account & Security.
 *
 * To generate a new digest:
 *   php -r "echo password_hash('NewStrongPassword', PASSWORD_DEFAULT), PHP_EOL;"
 */
define('DEFAULT_ADMIN_USERNAME', 'arafat_admin');
define('DEFAULT_ADMIN_PASSWORD_HASH', '$2y$10$w0uGfJVRPnp3NlI3tVVRzOhuRAyk.XfsMhAWCvs1xE6kgoC4Qh.5m');

/** Used to equalise response time when a username does not exist. */
define('DUMMY_PASSWORD_HASH', '$2y$10$5kvgG96me0jDvwHZVBNOfu8tLnzFe6tdFjJWSQYzDn2FUYtSTpSZ2');

// ---------------------------------------------------------------------------
//  Image uploads
// ---------------------------------------------------------------------------
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024); // 5 MB

/**
 * Allowed upload types. The extension is taken from this map — never from
 * the visitor-supplied filename — and the real MIME type is verified with
 * finfo before anything is moved into /uploads/trips.
 */
define('ALLOWED_IMAGE_MIMES', [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
]);

// ---------------------------------------------------------------------------
//  Public contact form
// ---------------------------------------------------------------------------
define('CONTACT_MIN_SECONDS', 2);        // reject instant bot submissions
define('CONTACT_MAX_PER_HOUR', 5);       // per browser session

// ---------------------------------------------------------------------------
//  Pagination
// ---------------------------------------------------------------------------
define('TRIPS_PER_PAGE', 8);
define('INQUIRIES_PER_PAGE', 10);

// ---------------------------------------------------------------------------
//  Locale
// ---------------------------------------------------------------------------
date_default_timezone_set(APP_TIMEZONE);
