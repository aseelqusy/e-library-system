<?php

class Order {

    private static function db(): PDO {
        return Database::getInstance();
    }

    public static function ensureTable(): void {
        try {
            logDebug('Order::ensureTable() checking orders table', [], 'orders');

            self::db()->exec(
                "CREATE TABLE IF NOT EXISTS orders (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    book_id INT NOT NULL,
                    quantity INT NOT NULL DEFAULT 1,
                    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    status ENUM('pending','paid','cancelled','refunded') NOT NULL DEFAULT 'paid',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_orders_user (user_id),
                    INDEX idx_orders_book (book_id),
                    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    CONSTRAINT fk_orders_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );

            logDebug('Orders table ensured successfully', [], 'orders');
        } catch (Throwable $e) {
            logError('Order::ensureTable() failed', $e, [], 'orders');
            throw $e;
        }
    }

    public static function create(int $userId, int $bookId, int $quantity, float $unitPrice, string $status = 'paid'): int {
        $quantity = max(1, $quantity);
        $unitPrice = max(0, $unitPrice);
        $total = round($quantity * $unitPrice, 2);

        logDebug('Order::create() called', [
            'user_id' => $userId,
            'book_id' => $bookId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $total,
            'status' => $status,
        ], 'orders');

        try {
            $stmt = self::db()->prepare(
                "INSERT INTO orders (user_id, book_id, quantity, unit_price, total_price, status) VALUES (?, ?, ?, ?, ?, ?)"
            );

            logDebug('Executing order insert statement', [
                'user_id' => $userId,
                'book_id' => $bookId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $total,
                'status' => $status,
            ], 'orders');

            $result = $stmt->execute([$userId, $bookId, $quantity, $unitPrice, $total, $status]);

            if (!$result) {
                logError('Order insert statement execute failed', null, [
                    'user_id' => $userId,
                    'book_id' => $bookId,
                    'error_info' => $stmt->errorInfo(),
                ], 'orders');
                throw new Exception('Insert statement execution failed: ' . json_encode($stmt->errorInfo()));
            }

            $lastId = (int)self::db()->lastInsertId();
            logDebug('Order inserted successfully', [
                'order_id' => $lastId,
                'user_id' => $userId,
            ], 'orders');

            return $lastId;
        } catch (Throwable $e) {
            logError('Order::create() failed', $e, [
                'user_id' => $userId,
                'book_id' => $bookId,
                'quantity' => $quantity,
            ], 'orders');
            throw $e;
        }
    }

    public static function forUser(int $userId): array {
        self::ensureTable();
        $stmt = self::db()->prepare(
            "SELECT o.*, b.title AS book_title, b.author AS book_author, b.isbn,
                    COALESCE(b.cover_image, b.cover) AS book_cover
             FROM orders o
             JOIN books b ON b.id = o.book_id
             WHERE o.user_id = ?
             ORDER BY o.id DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}

