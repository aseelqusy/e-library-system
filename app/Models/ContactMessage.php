<?php

class ContactMessage {

    private static function db(): PDO {
        return Database::getInstance();
    }

    public static function ensureTable(): void {
        self::db()->exec(
            "CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(180) NOT NULL,
                subject VARCHAR(180) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public static function create(array $data): int {
        self::ensureTable();
        $stmt = self::db()->prepare(
            "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            trim($data['name'] ?? ''),
            trim($data['email'] ?? ''),
            trim($data['subject'] ?? ''),
            trim($data['message'] ?? ''),
        ]);

        return (int)self::db()->lastInsertId();
    }
}

