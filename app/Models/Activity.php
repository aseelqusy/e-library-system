<?php

class Activity {

    private static function db(): PDO {
        return Database::getInstance();
    }

    public static function ensureTable(): void {
        self::db()->exec(
            "CREATE TABLE IF NOT EXISTS activities (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(180) NOT NULL,
                description TEXT NOT NULL,
                image_path VARCHAR(255) NULL,
                activity_date DATE NOT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_activity_date (activity_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public static function latest(int $limit = 6): array {
        self::ensureTable();
        $stmt = self::db()->prepare(
            "SELECT * FROM activities WHERE is_active = 1 ORDER BY activity_date DESC, id DESC LIMIT ?"
        );
        $stmt->bindValue(1, max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

