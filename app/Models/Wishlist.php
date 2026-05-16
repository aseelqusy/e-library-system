<?php

class Wishlist {

    private static function db(): PDO {
        return Database::getInstance();
    }

    public static function booksForUser(int $userId): array {
        $stmt = self::db()->prepare(
            "SELECT b.* FROM wishlists w JOIN books b ON b.id = w.book_id WHERE w.user_id = ? ORDER BY w.id DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function exists(int $userId, int $bookId): bool {
        $stmt = self::db()->prepare("SELECT id FROM wishlists WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$userId, $bookId]);
        return (bool)$stmt->fetch();
    }

    public static function add(int $userId, int $bookId): void {
        $stmt = self::db()->prepare("INSERT INTO wishlists (user_id, book_id) VALUES (?, ?)");
        $stmt->execute([$userId, $bookId]);
    }

    public static function remove(int $userId, int $bookId): void {
        $stmt = self::db()->prepare("DELETE FROM wishlists WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$userId, $bookId]);
    }

    public static function count(): int {
        $stmt = self::db()->query("SELECT COUNT(*) AS total FROM wishlists");
        return (int)($stmt->fetch()['total'] ?? 0);
    }
}
