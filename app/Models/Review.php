<?php

/**
 * Review Model (placeholder)
 *
 * Schema:
 *   reviews (id INT PK AUTO_INCREMENT, user_id INT FK, book_id INT FK,
 *            rating TINYINT, comment TEXT, created_at DATETIME)
 */
class Review {

    private static function db(): PDO {
        return Database::getInstance();
    }

    public static function all(): array {
        $stmt = self::db()->query("SELECT * FROM reviews ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public static function forBook(int $bookId): array {
        $stmt = self::db()->prepare("SELECT * FROM reviews WHERE book_id = ? ORDER BY id DESC");
        $stmt->execute([$bookId]);
        return $stmt->fetchAll();
    }

    public static function forUser(int $userId): array {
        $stmt = self::db()->prepare("SELECT * FROM reviews WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function averageForBook(int $bookId): float {
        $stmt = self::db()->prepare("SELECT AVG(rating) AS avg_rating FROM reviews WHERE book_id = ?");
        $stmt->execute([$bookId]);
        return (float)($stmt->fetch()['avg_rating'] ?? 0);
    }

    public static function create(int $userId, int $bookId, int $rating, string $comment): int {
        $stmt = self::db()->prepare(
            "INSERT INTO reviews (user_id, book_id, rating, comment) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $bookId, $rating, $comment]);
        return (int)self::db()->lastInsertId();
    }

    public static function count(): int {
        $stmt = self::db()->query("SELECT COUNT(*) AS total FROM reviews");
        return (int)($stmt->fetch()['total'] ?? 0);
    }
}
