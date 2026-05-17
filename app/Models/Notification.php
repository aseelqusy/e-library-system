<?php

/**
 * Notification Model (placeholder)
 *
 * Schema:
 *   notifications (id INT PK AUTO_INCREMENT, user_id INT FK,
 *                  type VARCHAR(50), message TEXT, is_read TINYINT DEFAULT 0,
 *                  created_at DATETIME)
 */
class Notification {

    private static function db(): PDO {
        return Database::getInstance();
    }

    public static function forUser(int $userId): array {
        $stmt = self::db()->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function unreadCount(int $userId): int {
        $stmt = self::db()->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public static function create(int $userId, string $message, string $type): void {
        $stmt = self::db()->prepare(
            "INSERT INTO notifications (user_id, type, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())"
        );
        $stmt->execute([$userId, $type, $message]);
    }
}
