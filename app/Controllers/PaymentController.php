<?php

class PaymentController extends Controller {

    public function show(string $id): void {
        $this->requireAuth();

        require_once APP_PATH . '/Models/Book.php';
        require_once APP_PATH . '/Models/Category.php';

        $bookId = (int)$id;
        if ($bookId <= 0) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Book Not Found']);
            return;
        }

        $book = Book::find($bookId);
        if (!$book) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Book Not Found']);
            return;
        }

        $book = Book::normalise($book);
        $quantity = max(1, min(10, (int)($_GET['quantity'] ?? 1)));

        // Get the price from the database
        $unitPrice = (float)($book['price'] ?? 0);

        // If no price is set in DB, calculate it from formula (for backward compatibility)
        if ($unitPrice <= 0) {
            $unitPrice = $this->resolvePrice($book);
        }

        if ($unitPrice <= 0) {
            setFlash('error', 'This book cannot be purchased right now.');
            $this->redirect('books/' . $bookId);
        }

        if ((int)($book['available'] ?? 0) <= 0) {
            setFlash('error', 'This book is currently unavailable for purchase.');
            $this->redirect('books/' . $bookId);
        }

        $category = !empty($book['category_id']) ? Category::find((int)$book['category_id']) : null;

        $this->view('payment/checkout', [
            'title'    => 'Checkout - ' . $book['title'],
            'book'     => $book,
            'category' => $category,
            'unitPrice' => $unitPrice,
            'quantity' => $quantity,
            'layout'   => 'public',
        ]);
    }

    public function process(): void {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            logDebug('Payment CSRF validation failed', [], 'payment');
            $this->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/Book.php';
        require_once APP_PATH . '/Models/Order.php';

        $bookId = (int)($_POST['book_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        $paymentMethod = trim($_POST['payment_method'] ?? 'card');
        $cardName = trim($_POST['card_name'] ?? '');
        $cardNumber = preg_replace('/\s+/', '', (string)($_POST['card_number'] ?? ''));
        $cardExpiry = trim($_POST['card_expiry'] ?? '');
        $cardCvv = trim($_POST['card_cvv'] ?? '');
        $cardZip = trim($_POST['card_zip'] ?? '');
        $userId = (int)Auth::id();

        logDebug('Payment processing started', [
            'user_id' => $userId,
            'book_id' => $bookId,
            'quantity' => $quantity,
            'payment_method' => $paymentMethod,
        ], 'payment');

        if ($bookId <= 0 || $quantity <= 0 || $quantity > 10) {
            logDebug('Invalid purchase details', [
                'book_id' => $bookId,
                'quantity' => $quantity,
            ], 'payment');
            $this->json(['success' => false, 'message' => 'Invalid purchase details.'], 422);
        }

        $book = Book::find($bookId);
        if (!$book) {
            logDebug('Book not found', ['book_id' => $bookId], 'payment');
            $this->json(['success' => false, 'message' => 'Book not found.'], 404);
        }

        $book = Book::normalise($book);
        logDebug('Book retrieved', [
            'book_id' => $bookId,
            'title' => $book['title'] ?? 'Unknown',
            'available' => $book['available'] ?? 0,
            'price' => $book['price'] ?? 0,
        ], 'payment');

        if ((int)($book['available'] ?? 0) < $quantity) {
            logDebug('Insufficient stock', [
                'book_id' => $bookId,
                'requested' => $quantity,
                'available' => $book['available'] ?? 0,
            ], 'payment');
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
            ], 'payment');
        }

        if ($unitPrice <= 0) {
            logDebug('Invalid unit price', [
                'book_id' => $bookId,
                'unit_price' => $unitPrice,
            ], 'payment');
            $this->json(['success' => false, 'message' => 'This book cannot be purchased right now.'], 422);
        }

        // Validate payment method
        if (!in_array($paymentMethod, ['card', 'paypal', 'transfer'], true)) {
            logDebug('Invalid payment method', ['payment_method' => $paymentMethod], 'payment');
            $this->json(['success' => false, 'message' => 'Invalid payment method.'], 422);
        }

        if ($paymentMethod === 'card') {
            if ($cardName === '' || $cardNumber === '' || $cardExpiry === '' || $cardCvv === '' || $cardZip === '') {
                $this->json(['success' => false, 'message' => 'Please fill in all card details before paying.'], 422);
            }

            if (!preg_match('/^\d{13,19}$/', $cardNumber)) {
                $this->json(['success' => false, 'message' => 'Card number must contain 13 to 19 digits.'], 422);
            }

            if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $cardExpiry)) {
                $this->json(['success' => false, 'message' => 'Card expiry must be in MM/YY format.'], 422);
            }

            if (!preg_match('/^\d{3,4}$/', $cardCvv)) {
                $this->json(['success' => false, 'message' => 'CVV must be 3 or 4 digits.'], 422);
            }
        }

        // Process payment (mock implementation)
        logDebug('Processing payment gateway', [
            'book_id' => $bookId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $quantity * $unitPrice,
            'payment_method' => $paymentMethod,
        ], 'payment');

        $paymentResult = $this->processPayment($bookId, $quantity, $unitPrice, $paymentMethod);

        if (!$paymentResult['success']) {
            logDebug('Payment gateway failed', [
                'book_id' => $bookId,
                'message' => $paymentResult['message'],
                'result' => $paymentResult,
            ], 'payment');
            $this->json(['success' => false, 'message' => $paymentResult['message']], 400);
        }

        logDebug('Payment gateway successful', [
            'transaction_id' => $paymentResult['transaction_id'] ?? 'unknown',
        ], 'payment');

        // Create order
        $db = Database::getInstance();
        try {
            Order::ensureTable();
            logDebug('Orders table verified before transaction', [], 'payment');

            $db->beginTransaction();
            logDebug('Transaction started', [], 'payment');

            logDebug('Creating order record', [
                'user_id' => $userId,
                'book_id' => $bookId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $quantity * $unitPrice,
            ], 'payment');

            $orderId = Order::create($userId, $bookId, $quantity, $unitPrice, 'paid');
            logDebug('Order created successfully', ['order_id' => $orderId], 'payment');

            logDebug('Adjusting book availability', [
                'book_id' => $bookId,
                'delta' => -$quantity,
            ], 'payment');

            Book::adjustAvailable($bookId, -$quantity);
            logDebug('Book availability adjusted', ['book_id' => $bookId], 'payment');

            $db->commit();
            logDebug('Transaction committed successfully', ['order_id' => $orderId], 'payment');

            $this->json([
                'success' => true,
                'message' => 'Payment processed successfully.',
                'order_id' => $orderId,
                'redirect' => url('user/orders'),
            ]);
        } catch (Throwable $e) {
            logError('Order creation failed during transaction', $e, [
                'user_id' => $userId,
                'book_id' => $bookId,
                'quantity' => $quantity,
            ], 'payment');

            if ($db->inTransaction()) {
                $db->rollBack();
                logDebug('Transaction rolled back', [], 'payment');
            }

            $this->json(['success' => false, 'message' => 'Failed to complete purchase. Please try again.'], 500);
        }
    }

    /**
     * Process payment via mock payment gateway
     * This is a mock implementation that can be easily extended to integrate with real payment providers
     */
    private function processPayment(int $bookId, int $quantity, float $unitPrice, string $paymentMethod): array {
        // In a real implementation, this would call Stripe, PayPal, or another payment gateway
        // For now, we simulate success with random chance (or always success for demo)

        // Mock validation for development
        $totalAmount = $quantity * $unitPrice;

        // Simulate payment processing
        // In production, this would make actual API calls to payment processors

        if ($paymentMethod === 'card') {
            // Mock credit card payment
            return [
                'success' => true,
                'message' => 'Card payment processed successfully',
                'transaction_id' => 'TXN-' . uniqid()
            ];
        } elseif ($paymentMethod === 'paypal') {
            // Mock PayPal payment
            return [
                'success' => true,
                'message' => 'PayPal payment processed successfully',
                'transaction_id' => 'PAYPAL-' . uniqid()
            ];
        } elseif ($paymentMethod === 'transfer') {
            // Bank transfer (may require manual verification)
            return [
                'success' => true,
                'message' => 'Bank transfer initiated. Pending verification.',
                'transaction_id' => 'TRANSFER-' . uniqid()
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid payment method'
        ];
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

