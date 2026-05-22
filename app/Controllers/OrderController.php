<?php

class OrderController extends Controller {

    public function buy(): void {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            logDebug('Order CSRF validation failed', [], 'orders');
            $this->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/Book.php';
        require_once APP_PATH . '/Models/Order.php';

        $bookId = (int)($_POST['book_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        $userId = (int)Auth::id();

        logDebug('Order buy request started', [
            'user_id' => $userId,
            'book_id' => $bookId,
            'quantity' => $quantity,
        ], 'orders');

        if ($bookId <= 0 || $quantity <= 0 || $quantity > 10) {
            logDebug('Invalid purchase details', [
                'book_id' => $bookId,
                'quantity' => $quantity,
            ], 'orders');
            $this->json(['success' => false, 'message' => 'Invalid purchase details.'], 422);
        }

        $book = Book::find($bookId);
        if (!$book) {
            logDebug('Book not found', ['book_id' => $bookId], 'orders');
            $this->json(['success' => false, 'message' => 'Book not found.'], 404);
        }

        $book = Book::normalise($book);
        logDebug('Book retrieved', [
            'book_id' => $bookId,
            'title' => $book['title'] ?? 'Unknown',
            'available' => $book['available'] ?? 0,
            'price' => $book['price'] ?? 0,
        ], 'orders');

        if ((int)($book['available'] ?? 0) < $quantity) {
            logDebug('Insufficient stock', [
                'book_id' => $bookId,
                'requested' => $quantity,
                'available' => $book['available'] ?? 0,
            ], 'orders');
            $this->json(['success' => false, 'message' => 'Not enough stock for this quantity.'], 409);
        }

        // Get the price from the database
        $unitPrice = (float)($book['price'] ?? 0);

        // If no price is set in DB, calculate it from formula (for backward compatibility)
        if ($unitPrice <= 0) {
            $unitPrice = $this->resolvePrice($book);
            logDebug('Price calculated from formula', [
                'book_id' => $bookId,
                'unit_price' => $unitPrice,
            ], 'orders');
        }

        if ($unitPrice <= 0) {
            logDebug('Invalid unit price', [
                'book_id' => $bookId,
                'unit_price' => $unitPrice,
            ], 'orders');
            $this->json(['success' => false, 'message' => 'This book cannot be purchased right now.'], 422);
        }

        logDebug('Creating order record', [
            'user_id' => $userId,
            'book_id' => $bookId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
        ], 'orders');

        $db = Database::getInstance();
        try {
            Order::ensureTable();
            logDebug('Orders table verified before transaction', [], 'orders');

            $db->beginTransaction();
            logDebug('Transaction started', [], 'orders');

            Order::create($userId, $bookId, $quantity, $unitPrice, 'paid');
            logDebug('Order created successfully', [
                'book_id' => $bookId,
                'quantity' => $quantity,
            ], 'orders');

            logDebug('Adjusting book availability', [
                'book_id' => $bookId,
                'delta' => -$quantity,
            ], 'orders');

            Book::adjustAvailable($bookId, -$quantity);
            logDebug('Book availability adjusted', ['book_id' => $bookId], 'orders');

            $db->commit();
            logDebug('Transaction committed successfully', ['book_id' => $bookId], 'orders');
        } catch (Throwable $e) {
            logError('Order creation failed', $e, [
                'user_id' => $userId,
                'book_id' => $bookId,
                'quantity' => $quantity,
            ], 'orders');

            if ($db->inTransaction()) {
                $db->rollBack();
                logDebug('Transaction rolled back', [], 'orders');
            }

            $this->json(['success' => false, 'message' => 'Failed to complete purchase.'], 500);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Purchase completed successfully.',
            'redirect' => url('user/orders'),
        ]);
    }

    private function resolvePrice(array $book): float {
        $pages = max(0, (int)($book['pages'] ?? 0));
        $rating = max(0, min(5, (float)($book['rating'] ?? 0)));

        $base = 8.50;
        $pageComponent = min(14.00, $pages * 0.02);
        $ratingComponent = $rating * 0.5;

        return round($base + $pageComponent + $ratingComponent, 2);
    }
}

