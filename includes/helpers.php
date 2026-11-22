<?php

declare(strict_types=1);

/**
 * ============================================================
 *  Shared helper functions
 * ============================================================
 *  Small, stateless utilities used by every page. Keeping them
 *  here means the templates stay readable and every value that
 *  reaches HTML goes through the same escaper, e().
 * ============================================================
 */

/**
 * Escape a value for safe use inside HTML text, attributes and URLs.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Public base path of the installation, e.g. "" at a domain root or
 * "/my-portfolio" inside a sub-folder. Auto-detected from DOCUMENT_ROOT.
 */
function app_base_url(): string
{
    static $base = null;

    if ($base !== null) {
        return $base;
    }

    $override = getenv('APP_BASE_URL');
    if (is_string($override) && $override !== '') {
        return $base = rtrim($override, '/');
    }

    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath((string) $_SERVER['DOCUMENT_ROOT']) : false;
    $appRoot = realpath(BASE_PATH);

    if ($docRoot !== false && $appRoot !== false) {
        $docRoot = rtrim(str_replace('\\', '/', $docRoot), '/');
        $appRoot = rtrim(str_replace('\\', '/', $appRoot), '/');

        if ($appRoot === $docRoot) {
            return $base = '';
        }

        if (str_starts_with($appRoot, $docRoot . '/')) {
            return $base = substr($appRoot, strlen($docRoot));
        }
    }

    return $base = '';
}

/**
 * Build a root-absolute URL for a project-relative path.
 * Example: url('admin/login.php') → "/admin/login.php"
 */
function url(string $path = ''): string
{
    $base = app_base_url();
    $path = ltrim($path, '/');

    return $path === '' ? ($base === '' ? '/' : $base . '/') : $base . '/' . $path;
}

/**
 * URL for a page inside the admin panel.
 */
function admin_url(string $path = ''): string
{
    return url('admin/' . ltrim($path, '/'));
}

/**
 * Resolve a stored image path to a usable public URL. External URLs are
 * returned untouched; local paths are prefixed with the installation base.
 */
function image_url(?string $path): string
{
    $path = trim((string) $path);

    if ($path === '') {
        return url(PLACEHOLDER_IMAGE);
    }

    if (preg_match('#^https?://#i', $path) === 1) {
        return $path;
    }

    return url($path);
}

/**
 * Send a Location header and stop execution.
 */
function redirect(string $location): void
{
    // Never allow a header-injection attempt through a redirect target.
    $location = str_replace(["\r", "\n"], '', $location);

    if (!headers_sent()) {
        header('Location: ' . $location, true, 302);
    }

    exit;
}

function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * Read a trimmed string from an input array (POST or GET).
 */
function input_string(array $source, string $key, string $default = ''): string
{
    $value = $source[$key] ?? $default;

    return is_scalar($value) ? trim((string) $value) : $default;
}

/**
 * Convert a stored UTC SQLite timestamp to the configured local timezone.
 */
function local_datetime(?string $utc, string $format = 'j M Y, g:i A'): string
{
    $utc = trim((string) $utc);

    if ($utc === '') {
        return '—';
    }

    try {
        $date = new DateTimeImmutable($utc, new DateTimeZone('UTC'));

        return $date->setTimezone(new DateTimeZone(APP_TIMEZONE))->format($format);
    } catch (Exception) {
        return $utc;
    }
}

/**
 * Convert a Y-m-d date string to a human-readable date.
 */
function format_date(?string $date, string $format = 'j M Y'): string
{
    $date = trim((string) $date);

    if ($date === '') {
        return '—';
    }

    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

    if ($parsed === false) {
        return $date;
    }

    return $parsed->format($format);
}

/**
 * Shorten long text for tables. The result is still raw and must be
 * escaped with e() at the point of output.
 */
function excerpt(string $text, int $limit = 120): string
{
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
    }

    return rtrim(mb_substr($text, 0, $limit, 'UTF-8')) . '…';
}

/**
 * Client IP, truncated to the usual 45-character maximum.
 * Used only for login throttling; never displayed publicly.
 */
function client_ip(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

    return substr($ip, 0, 45) ?: 'unknown';
}

/**
 * Baseline security headers for every PHP response.
 * The CSP allows Bootstrap, Bootstrap Icons and Google Fonts from CDNs,
 * but no third-party scripts. No inline JavaScript is used anywhere.
 */
function send_security_headers(): void
{
    if (PHP_SAPI === 'cli' || headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
    header(
        "Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; "
        . "frame-ancestors 'self'; object-src 'none'; img-src 'self' data:; "
        . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; "
        . "font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com; "
        . "script-src 'self' https://cdn.jsdelivr.net; connect-src 'self'"
    );
}

/**
 * Emit a JSON response and stop.
 */
function json_response(array $payload, int $status = 200): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Minimal error page for unrecoverable request problems (404, 405, 419...).
 */
function abort_request(int $status, string $title, string $message = ''): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
    }

    $home = url();
    $labels = [
        404 => 'Not found',
        405 => 'Method not allowed',
        419 => 'Session expired',
        500 => 'Server error',
    ];
    $heading = $title !== '' ? $title : ($labels[$status] ?? 'Error');

    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . e($heading) . ' · ' . e(APP_NAME) . '</title>'
        . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">'
        . '</head><body class="bg-dark text-light d-flex align-items-center" style="min-height:100vh">'
        . '<main class="container py-5" style="max-width:640px">'
        . '<p class="text-uppercase small text-warning mb-2">Error ' . e((string) $status) . '</p>'
        . '<h1 class="h3 mb-3">' . e($heading) . '</h1>'
        . ($message !== '' ? '<p class="text-secondary">' . e($message) . '</p>' : '')
        . '<a class="btn btn-outline-warning mt-3" href="' . e($home) . '">Back to the site</a>'
        . '</main></body></html>';
    exit;
}

/**
 * Build a pagination control. Bootstrap 5 markup, accessible labels.
 *
 * @param array<string, scalar> $query Current query string values (page excluded).
 */
function pagination_links(int $page, int $totalPages, array $query = []): string
{
    if ($totalPages <= 1) {
        return '';
    }

    $makeUrl = static function (int $target) use ($query): string {
        $query['page'] = $target;
        $query = array_filter($query, static fn ($value): bool => $value !== '' && $value !== null);

        return '?' . http_build_query($query);
    };

    $windowStart = max(1, min($page - 2, $totalPages - 4));
    $windowEnd = min($totalPages, $windowStart + 4);

    $html = '<nav aria-label="Pagination"><ul class="pagination admin-pagination mb-0">';

    $html .= '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '">'
        . '<a class="page-link" href="' . e($page <= 1 ? '#' : $makeUrl($page - 1)) . '"'
        . ($page <= 1 ? ' tabindex="-1" aria-disabled="true"' : '') . '>Previous</a></li>';

    if ($windowStart > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . e($makeUrl(1)) . '">1</a></li>';
        if ($windowStart > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
    }

    for ($i = $windowStart; $i <= $windowEnd; $i++) {
        $isCurrent = $i === $page;
        $html .= '<li class="page-item' . ($isCurrent ? ' active' : '') . '">'
            . '<a class="page-link" href="' . e($makeUrl($i)) . '"'
            . ($isCurrent ? ' aria-current="page"' : '') . '>' . e((string) $i) . '</a></li>';
    }

    if ($windowEnd < $totalPages) {
        if ($windowEnd < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . e($makeUrl($totalPages)) . '">'
            . e((string) $totalPages) . '</a></li>';
    }

    $html .= '<li class="page-item' . ($page >= $totalPages ? ' disabled' : '') . '">'
        . '<a class="page-link" href="' . e($page >= $totalPages ? '#' : $makeUrl($page + 1)) . '"'
        . ($page >= $totalPages ? ' tabindex="-1" aria-disabled="true"' : '') . '>Next</a></li>';

    return $html . '</ul></nav>';
}
