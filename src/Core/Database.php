<?php

declare(strict_types=1);

namespace Accelio\Core;

use PDO;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $app = Application::getInstance();
        $storagePath = $app->basePath('storage');

        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $dbPath = $storagePath . '/database.sqlite';
        $isNew = !file_exists($dbPath);

        self::$connection = new PDO(
            'sqlite:' . $dbPath,
            options: [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );

        // Enable WAL mode for better concurrent access
        self::$connection->exec('PRAGMA journal_mode=WAL');

        if ($isNew) {
            self::migrate();
        }

        return self::$connection;
    }

    private static function migrate(): void
    {
        self::$connection->exec('
            CREATE TABLE IF NOT EXISTS documents (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                type TEXT NOT NULL DEFAULT \'text\',
                content TEXT,
                file_path TEXT,
                created_at TEXT DEFAULT (datetime(\'now\')),
                updated_at TEXT DEFAULT (datetime(\'now\'))
            )
        ');
    }
}
