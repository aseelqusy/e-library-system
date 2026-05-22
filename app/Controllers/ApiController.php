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

    public function reviews(string $bookId): void {
        require_once APP_PATH . '/Models/Review.php';
        if (!ctype_digit($bookId)) {
            $this->json(['error' => 'Invalid book.'], 422);
        }

        $bookIdInt = (int)$bookId;
        $userId = Auth::id();
        $reviews = Review::forBook($bookIdInt, $userId);
        $stats = Review::statsForBook($bookIdInt);

        $this->json([
            'success' => true,
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
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

        $review = Review::create(Auth::id(), $bookId, $rating, $comment);
        $reviews = Review::forBook($bookId, Auth::id());
        $stats = Review::statsForBook($bookId);

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
            'message' => 'Review saved successfully.',
            'review'  => $review,
            'reviews' => $reviews,
            'stats'   => $stats,
        ]);
    }

    public function updateReview(string $id): void {
        if (!Auth::check()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/Review.php';
        $reviewId = (int)$id;
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($reviewId <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
            $this->json(['error' => 'Invalid review data.'], 422);
        }

        $review = Review::findById($reviewId);
        if (!$review || (int)$review['user_id'] !== Auth::id()) {
            $this->json(['error' => 'You can only edit your own review.'], 403);
        }

        if (!Review::update($reviewId, Auth::id(), $rating, $comment)) {
            $this->json(['error' => 'Unable to update review.'], 500);
        }

        $reviews = Review::forBook((int)$review['book_id'], Auth::id());
        $stats = Review::statsForBook((int)$review['book_id']);

        $this->json([
            'success' => true,
            'message' => 'Review updated successfully.',
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }

    public function deleteReview(string $id): void {
        if (!Auth::check()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/Review.php';
        $reviewId = (int)$id;
        if ($reviewId <= 0) {
            $this->json(['error' => 'Invalid review.'], 422);
        }

        $review = Review::findById($reviewId);
        if (!$review || (int)$review['user_id'] !== Auth::id()) {
            $this->json(['error' => 'You can only delete your own review.'], 403);
        }

        if (!Review::delete($reviewId, Auth::id())) {
            $this->json(['error' => 'Unable to delete review.'], 500);
        }

        $reviews = Review::forBook((int)$review['book_id'], Auth::id());
        $stats = Review::statsForBook((int)$review['book_id']);

        $this->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }

    public function notifications(): void {
        if (!Auth::check()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        require_once APP_PATH . '/Models/Notification.php';
        $userId = Auth::id();

        $this->json([
            'success' => true,
            'unreadCount' => Notification::unreadCount($userId),
            'notifications' => Notification::latestForUser($userId, 10),
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


    /**
     * Bulk cache book covers from OpenLibrary
     * Admin-only endpoint
     */
    public function cacheCovers(): void {
        // Admin check
        if (!Auth::check() || Auth::user()['role'] !== 'admin') {
            $this->json(['error' => 'Unauthorized'], 403);
        }

        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/Book.php';

        try {
            // Get all books with ISBN
            $allBooks = Book::all();
            $isbns = [];

            foreach ($allBooks as $book) {
                $isbn = $book['isbn'] ?? null;
                if (!empty($isbn)) {
                    $isbns[] = $isbn;
                }
            }

            if (empty($isbns)) {
                $this->json([
                    'success' => true,
                    'message' => 'No books with ISBN found',
                    'results' => ['cached' => 0, 'skipped' => 0, 'failed' => 0],
                ]);
            }

            // Batch cache all covers
            $results = batch_cache_covers($isbns);

            logDebug("Bulk cover cache completed", $results, 'book-covers');

            $this->json([
                'success' => true,
                'message' => "Caching complete. Cached: {$results['cached']}, Skipped: {$results['skipped']}, Failed: {$results['failed']}",
                'results' => $results,
            ]);
        } catch (Throwable $e) {
            logError("Bulk cache covers failed", $e, [], 'book-covers');
            $this->json(['error' => 'Cache operation failed.'], 500);
        }
    }
}
