<?php

declare(strict_types=1);

/**
 * Database schema.
 *
 * ensure() is idempotent: it runs on every first connection and uses
 * CREATE TABLE IF NOT EXISTS, so regenerating the .sqlite file or
 * upgrading the project never destroys existing data.
 */
final class Schema
{
    public static function ensure(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS admins (
                id            INTEGER PRIMARY KEY AUTOINCREMENT,
                username      TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                created_at    TEXT NOT NULL DEFAULT (datetime(\'now\'))
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS trips (
                id               INTEGER PRIMARY KEY AUTOINCREMENT,
                destination_name TEXT NOT NULL,
                country          TEXT NOT NULL,
                description      TEXT NOT NULL,
                price_or_budget  TEXT NOT NULL DEFAULT \'\',
                travel_date      TEXT NOT NULL,
                image_path       TEXT DEFAULT NULL,
                created_at       TEXT NOT NULL DEFAULT (datetime(\'now\'))
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS inquiries (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                sender_name TEXT NOT NULL,
                email       TEXT NOT NULL,
                message     TEXT NOT NULL,
                created_at  TEXT NOT NULL DEFAULT (datetime(\'now\'))
            )'
        );

        // Extra table used only for brute-force protection on the login form.
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS login_attempts (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                username     TEXT NOT NULL,
                ip           TEXT NOT NULL,
                attempted_at TEXT NOT NULL DEFAULT (datetime(\'now\'))
            )'
        );

        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_trips_travel_date ON trips (travel_date DESC)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_inquiries_created_at ON inquiries (created_at DESC)');
        $pdo->exec(
            'CREATE INDEX IF NOT EXISTS idx_login_attempts_lookup
             ON login_attempts (username, ip, attempted_at)'
        );
    }
}
