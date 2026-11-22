<?php

declare(strict_types=1);

/**
 * One-time flash messages stored in the session and rendered by the
 * admin layout. Values are auto-escaped by the layout.
 */
final class Flash
{
    private const SESSION_KEY = '_flash';
    private const TYPES = ['success', 'error', 'warning', 'info'];

    private function __construct()
    {
    }

    public static function add(string $type, string $message): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION[self::SESSION_KEY][] = [
            'type'    => in_array($type, self::TYPES, true) ? $type : 'info',
            'message' => $message,
        ];
    }

    /**
     * Return all pending messages and clear them.
     *
     * @return array<int, array{type: string, message: string}>
     */
    public static function pull(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return [];
        }

        $messages = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]);

        return is_array($messages) ? $messages : [];
    }
}
