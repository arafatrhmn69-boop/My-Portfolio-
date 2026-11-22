<?php

declare(strict_types=1);

/**
 * PDO/SQLite connection holder.
 *
 * One connection per request, configured with:
 *  - exceptions on error (never silent failures)
 *  - real prepared statements (ATTR_EMULATE_PREPARES = false)
 *  - foreign keys on, WAL journaling, a 5s busy timeout
 *
 * The schema is ensured lazily on first connection, so a missing
 * database/portfolio.sqlite file is created automatically.
 */
final class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $directory = dirname(DB_PATH);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create the database directory: ' . $directory);
        }

        $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ]);

        // Sensible SQLite pragmas for a small, low-traffic portfolio site.
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');

        Schema::ensure($pdo);

        return self::$connection = $pdo;
    }

    /**
     * True when the SQLite file already exists (used by the installer).
     */
    public static function exists(): bool
    {
        return is_file(DB_PATH);
    }
}
