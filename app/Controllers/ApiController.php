<?php

class ApiController extends Controller {

    public function books(): void {
        require_once APP_PATH . '/Models/Book.php';
        $this->json(['data' => array_values(Book::all())]);
    }

    public function categories(): void {
        require_once APP_PATH . '/Models/Category.php';
        $this->json(['data' => array_values(Category::all())]);
    }

    public function featured(): void {
        require_once APP_PATH . '/Models/Book.php';
        $this->json(['data' => array_values(Book::featured())]);
    }

    public function search(): void {
        require_once APP_PATH . '/Models/Book.php';
        $q = $_GET['q'] ?? '';
        $results = $q ? array_values(Book::search($q)) : [];
        $this->json(['data' => $results, 'query' => $q, 'count' => count($results)]);
    }

    public function addReview(): void {
        if (!Auth::check()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/Review.php';
        require_once APP_PATH . '/Models/Book.php';

        $bookId  = (int)($_POST['book_id'] ?? 0);
        $rating  = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($bookId <= 0 || $rating < 1 || $rating > 5 || !$comment) {
            $this->json(['error' => 'Invalid review data.'], 422);
        }

        Review::create(Auth::id(), $bookId, $rating, $comment);

        try {
            require_once APP_PATH . '/Models/Notification.php';
            $adminId = 1;
            $book = Book::find($bookId);
            $title = $book['title'] ?? 'Unknown Title';
            $name = Auth::user()['name'] ?? 'Unknown User';
            Notification::create(
                $adminId,
                "⭐ New Review! User '{$name}' left a comment and rating on: '{$title}'.",
                'admin_alert'
            );
        } catch (Throwable $e) {
            error_log('Failed to send review notification: ' . $e->getMessage());
        }

        $this->json([
            'success' => true,
            'message' => 'Review submitted!',
            'review'  => [
                'user'    => Auth::user()['name'],
                'rating'  => $rating,
                'comment' => $comment,
                'date'    => date('M d, Y'),
            ],
        ]);
    }

    public function markNotificationsRead(): void {
        if (!Auth::check()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/Notification.php';
        $userId = Auth::id();
        try {
            Notification::markAllReadForUser($userId);
            $this->json(['success' => true, 'message' => 'Notifications marked as read.']);
        } catch (Throwable $e) {
            error_log('Failed to mark notifications read: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to mark notifications.'], 500);
        }
    }

    /**
     * Lightweight API endpoint to clear (mark read) all notifications for the
     * authenticated user. Intended for background calls from the navbar UI.
     */
    public function clearNotifications(): void {
        if (!Auth::check()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/Notification.php';
        $userId = Auth::id();
        try {
            // Use the new model method name; this will fall back to the old one
            // if needed since the model provides an alias.
            Notification::markAllAsRead($userId);
            $this->json(['success' => true]);
        } catch (Throwable $e) {
            error_log('Failed to clear notifications: ' . $e->getMessage());
            $this->json(['success' => false], 500);
        }
    }

    public function book(string $id): void {
        require_once APP_PATH . '/Models/Book.php';
        require_once APP_PATH . '/Models/Category.php';

        $book = Book::find((int)$id);
        if (!$book) {
            $this->json(['error' => 'Book not found.'], 404);
        }

        $category = !empty($book['category_id']) ? Category::find((int)$book['category_id']) : null;

        $this->json([
            'data' => $book,
            'category' => $category,
        ]);
    }
}
