<?php

class Quote {

    private static function db(): PDO {
        return Database::getInstance();
    }

    public static function ensureTable(): void {
        self::db()->exec(
            "CREATE TABLE IF NOT EXISTS quotes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                quote_text TEXT NOT NULL,
                quote_author VARCHAR(160) NULL,
                source VARCHAR(160) NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public static function active(int $limit = 8): array {
        self::ensureTable();
        $stmt = self::db()->prepare(
            "SELECT id, quote_text, quote_author, source FROM quotes WHERE is_active = 1 ORDER BY id DESC LIMIT ?"
        );
        $stmt->bindValue(1, max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

