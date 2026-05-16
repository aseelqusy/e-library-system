<?php

class Borrow {

    private static function db(): PDO {
        return Database::getInstance();
    }

    public static function all(): array {
        $stmt = self::db()->query("SELECT * FROM borrows ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $stmt = self::db()->prepare("SELECT * FROM borrows WHERE id = ?");
        $stmt->execute([$id]);
        $borrow = $stmt->fetch();
        return $borrow ?: null;
    }

    public static function forUser(int $userId): array {
        $stmt = self::db()->prepare("SELECT * FROM borrows WHERE user_id = ? ORDER BY borrow_date DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function active(): array {
        $stmt = self::db()->prepare("SELECT * FROM borrows WHERE status = 'active' ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function overdue(): array {
        $stmt = self::db()->prepare("SELECT * FROM borrows WHERE status = 'overdue' ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count(): int {
        $stmt = self::db()->query("SELECT COUNT(*) AS total FROM borrows");
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public static function todayCount(): int {
        $stmt = self::db()->prepare("SELECT COUNT(*) AS total FROM borrows WHERE borrow_date = CURDATE()");
        $stmt->execute();
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public static function create(int $userId, int $bookId, string $status, ?string $borrowDate, ?string $dueDate): int {
        $stmt = self::db()->prepare(
            "INSERT INTO borrows (user_id, book_id, status, borrow_date, due_date) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $bookId, $status, $borrowDate, $dueDate]);
        return (int)self::db()->lastInsertId();
    }

    public static function updateStatus(int $id, string $status, ?string $returnDate = null, ?string $borrowDate = null, ?string $dueDate = null): bool {
        $stmt = self::db()->prepare(
            "UPDATE borrows SET status = ?, return_date = ?, borrow_date = COALESCE(?, borrow_date), due_date = COALESCE(?, due_date) WHERE id = ?"
        );
        return $stmt->execute([$status, $returnDate, $borrowDate, $dueDate, $id]);
    }

    public static function recentWithDetails(int $limit = 6): array {
        $stmt = self::db()->prepare(
            "SELECT b.*, u.name AS user_name, bo.title AS book_title
             FROM borrows b
             JOIN users u ON u.id = b.user_id
             JOIN books bo ON bo.id = b.book_id
             ORDER BY b.created_at DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countByMonth(int $months = 12): array {
        $months = max(1, $months);
        $start = (new DateTimeImmutable('first day of this month'))->modify('-' . ($months - 1) . ' months');
        $end = (new DateTimeImmutable('first day of next month'));

        $stmt = self::db()->prepare(
            "SELECT DATE_FORMAT(borrow_date, '%Y-%m') AS ym, COUNT(*) AS total
             FROM borrows
             WHERE borrow_date >= ? AND borrow_date < ?
             GROUP BY ym"
        );
        $stmt->execute([$start->format('Y-m-d'), $end->format('Y-m-d')]);
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['ym']] = (int)$row['total'];
        }

        $labels = [];
        $data = [];
        for ($i = 0; $i < $months; $i++) {
            $month = $start->modify('+' . $i . ' months');
            $key = $month->format('Y-m');
            $labels[] = $month->format('M');
            $data[] = $map[$key] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
