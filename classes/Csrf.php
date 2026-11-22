<?php

declare(strict_types=1);

/**
 * Synchronizer-token CSRF protection for every state-changing request.
 *
 * The token is stored in the session, embedded in forms via field(), and
 * compared with hash_equals() so the check is timing-safe. Tokens rotate
 * after login and after a password change.
 */
final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    private function __construct()
    {
    }

    public static function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('A PHP session is required for CSRF protection.');
        }

        $token = $_SESSION[self::SESSION_KEY] ?? null;

        if (!is_string($token) || strlen($token) < 32) {
            $token = bin2hex(random_bytes(32));
            $_SESSION[self::SESSION_KEY] = $token;
        }

        return $token;
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate(?string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $stored = $_SESSION[self::SESSION_KEY] ?? null;

        return is_string($stored)
            && is_string($token)
            && $token !== ''
            && hash_equals($stored, $token);
    }

    /**
     * Abort the request with a 419 page when the submitted token is missing
     * or wrong. Call this before processing any POST request.
     */
    public static function requireValid(): void
    {
        $token = $_POST['_csrf'] ?? null;

        if (self::validate(is_string($token) ? $token : null)) {
            return;
        }

        abort_request(
            419,
            'Session expired',
            'Your security token is missing or has expired. Go back, reload the page and submit the form again.'
        );
    }

    public static function rotate(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }
}
