<?php

/**
 * Review model with CRUD, ownership checks, and rating synchronization.
 */
class Review {

    private static function db(): PDO {
        return Database::getInstance();
    }

    private static function selectBase(): string {
        return "SELECT r.*, u.name AS user_name, u.avatar AS user_avatar, b.title AS book_title\n"
            . "        FROM reviews r\n"
            . "        JOIN users u ON u.id = r.user_id\n"
            . "        JOIN books b ON b.id = r.book_id";
    }

    private static function mapReview(array $review, ?int $currentUserId = null): array {
        $review['is_owner'] = $currentUserId !== null && (int)($review['user_id'] ?? 0) === $currentUserId;
        return $review;
    }

    public static function all(): array {
        $stmt = self::db()->query(self::selectBase() . " ORDER BY r.id DESC");
        return array_map(fn($review) => self::mapReview($review), $stmt->fetchAll());
    }

    public static function forBook(int $bookId, ?int $currentUserId = null): array {
        $stmt = self::db()->prepare(self::selectBase() . " WHERE r.book_id = ? ORDER BY r.id DESC");
        $stmt->execute([$bookId]);
        return array_map(fn($review) => self::mapReview($review, $currentUserId), $stmt->fetchAll());
    }

    public static function forUser(int $userId): array {
        $stmt = self::db()->prepare(self::selectBase() . " WHERE r.user_id = ? ORDER BY r.id DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array {
        $stmt = self::db()->prepare(self::selectBase() . " WHERE r.id = ?");
        $stmt->execute([$id]);
        $review = $stmt->fetch();
        return $review ?: null;
    }

    public static function findByBookAndUser(int $bookId, int $userId): ?array {
        $stmt = self::db()->prepare(self::selectBase() . " WHERE r.book_id = ? AND r.user_id = ? LIMIT 1");
        $stmt->execute([$bookId, $userId]);
        $review = $stmt->fetch();
        return $review ?: null;
    }

    public static function averageForBook(int $bookId): float {
        $stmt = self::db()->prepare("SELECT AVG(rating) AS avg_rating FROM reviews WHERE book_id = ?");
        $stmt->execute([$bookId]);
        return (float)($stmt->fetch()['avg_rating'] ?? 0);
    }

    public static function statsForBook(int $bookId): array {
        $stmt = self::db()->prepare("SELECT COUNT(*) AS total_reviews, COALESCE(AVG(rating), 0) AS avg_rating FROM reviews WHERE book_id = ?");
        $stmt->execute([$bookId]);
        $row = $stmt->fetch() ?: [];

        return [
            'count' => (int)($row['total_reviews'] ?? 0),
            'average' => round((float)($row['avg_rating'] ?? 0), 1),
        ];
    }

    private static function syncBookRating(int $bookId): void {
        require_once APP_PATH . '/Models/Book.php';
        $stats = self::statsForBook($bookId);
        Book::updateRating($bookId, $stats['average']);
    }

    public static function create(int $userId, int $bookId, int $rating, string $comment): array {
        $existing = self::findByBookAndUser($bookId, $userId);
        if ($existing) {
            self::update((int)$existing['id'], $userId, $rating, $comment);
            return self::findById((int)$existing['id']) ?: [];
        }

        $stmt = self::db()->prepare(
            "INSERT INTO reviews (user_id, book_id, rating, comment) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $bookId, $rating, $comment]);
        $reviewId = (int)self::db()->lastInsertId();

        self::syncBookRating($bookId);

        return self::findById($reviewId) ?: [];
    }

    public static function update(int $id, int $userId, int $rating, string $comment): bool {
        $review = self::findById($id);
        if (!$review || (int)$review['user_id'] !== $userId) {
            return false;
        }

        $stmt = self::db()->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE id = ? AND user_id = ?");
        $ok = $stmt->execute([$rating, $comment, $id, $userId]);

        if ($ok) {
            self::syncBookRating((int)$review['book_id']);
        }

        return $ok;
    }

    public static function delete(int $id, int $userId): bool {
        $review = self::findById($id);
        if (!$review || (int)$review['user_id'] !== $userId) {
            return false;
        }

        $stmt = self::db()->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
        $ok = $stmt->execute([$id, $userId]);

        if ($ok) {
            self::syncBookRating((int)$review['book_id']);
        }

        return $ok;
    }

    public static function count(): int {
        $stmt = self::db()->query("SELECT COUNT(*) AS total FROM reviews");
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public static function listForAdmin(string $search = '', string $rating = '', int $page = 1, int $perPage = 12): array {
        $search = trim($search);
        $ratingInt = (int)$rating;
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = '(u.name LIKE ? OR b.title LIKE ? OR r.comment LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($ratingInt >= 1 && $ratingInt <= 5) {
            $where[] = 'r.rating = ?';
            $params[] = $ratingInt;
        }

        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = self::db()->prepare(
            "SELECT COUNT(*) AS total
             FROM reviews r
             JOIN users u ON u.id = r.user_id
             JOIN books b ON b.id = r.book_id" . $whereSql
        );
        $countStmt->execute($params);
        $total = (int)($countStmt->fetch()['total'] ?? 0);

        $sql = "SELECT r.*, u.name AS user_name, b.title AS book_title
                FROM reviews r
                JOIN users u ON u.id = r.user_id
                JOIN books b ON b.id = r.book_id"
            . $whereSql
            . " ORDER BY r.id DESC LIMIT {$perPage} OFFSET {$offset}";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return [
            'data' => $rows,
            'total' => $total,
            'page' => $page,
            'pages' => max(1, (int)ceil($total / $perPage)),
            'perPage' => $perPage,
        ];
    }

    public static function deleteByAdmin(int $id): bool {
        $review = self::findById($id);
        if (!$review) {
            return false;
        }

        $stmt = self::db()->prepare('DELETE FROM reviews WHERE id = ?');
        $ok = $stmt->execute([$id]);
        if ($ok) {
            self::syncBookRating((int)$review['book_id']);
        }
        return $ok;
    }
}
