<?php

class UserController extends Controller {

    public function profile(): void {
        $this->requireAuth();
        require_once APP_PATH . '/Models/User.php';
        $user = User::find(Auth::id());

        $this->view('user/profile', [
            'title'   => 'My Profile',
            'user'    => $user,
            'layout'  => 'public',
            'current_page' => 'profile',
        ]);
    }

    public function updateProfile(): void {
        $this->requireAuth();
        // Allow this single endpoint to dispatch multiple profile-related POST actions
        $action = $_POST['action'] ?? 'update_profile';

        if ($action === 'update_password') {
            // Delegate to the password update flow which performs its own CSRF check
            $this->updatePassword();
            return;
        }

        if ($action === 'delete_account') {
            // Delegate to the account deletion flow which performs its own CSRF check
            $this->deleteAccount();
            return;
        }

        // Default: update profile details
        if (!$this->validateCsrf()) {
            setFlash('error', 'Invalid request.');
            $this->redirect('user/profile');
        }

        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $bio   = trim($_POST['bio'] ?? '');

        if ($name === '' || $email === '') {
            setFlash('error', 'Name and email are required.');
            $this->redirect('user/profile');
        }

        require_once APP_PATH . '/Models/User.php';

        if (User::findByEmail($email) && strtolower($email) !== strtolower(Auth::user()['email'] ?? '')) {
            setFlash('error', 'Email already in use by another account.');
            $this->redirect('user/profile');
        }

        User::updateProfile(Auth::id(), [
            'name' => $name, 'email' => $email,
            'phone' => $phone, 'bio' => $bio,
        ]);

        setFlash('success', 'Profile updated successfully.');
        $this->redirect('user/profile');
    }

    public function borrows(): void {
        $this->requireAuth();
        require_once APP_PATH . '/Models/Borrow.php';
        require_once APP_PATH . '/Models/Book.php';

        $borrows = Borrow::forUser(Auth::id());
        $enriched = [];
        foreach ($borrows as $b) {
            $b['book'] = Book::find($b['book_id']);
            $enriched[] = $b;
        }

        $this->view('user/my-borrows', [
            'title'   => 'My Borrows',
            'borrows' => $enriched,
            'layout'  => 'public',
            'current_page' => 'borrows',
        ]);
    }

    public function wishlist(): void {
        $this->requireAuth();
        require_once APP_PATH . '/Models/Wishlist.php';

        $books = Wishlist::booksForUser(Auth::id());

        $this->view('user/wishlist', [
            'title'  => 'My Wishlist',
            'books'  => $books,
            'layout' => 'public',
            'current_page' => 'wishlist',
        ]);
    }

    public function toggleWishlist(): void {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/Wishlist.php';

        $bookId = (int)($_POST['book_id'] ?? 0);
        if ($bookId <= 0) {
            $this->json(['status' => 'error', 'message' => 'Invalid book.'], 422);
        }

        if (Wishlist::exists(Auth::id(), $bookId)) {
            Wishlist::remove(Auth::id(), $bookId);
            $this->json(['status' => 'removed', 'message' => 'Removed from wishlist', 'book_id' => $bookId]);
        } else {
            Wishlist::add(Auth::id(), $bookId);
            $this->json(['status' => 'added', 'message' => 'Added to wishlist', 'book_id' => $bookId]);
        }
    }

    public function history(): void {
        $this->requireAuth();
        require_once APP_PATH . '/Models/Borrow.php';
        require_once APP_PATH . '/Models/Book.php';

        $all = Borrow::forUser(Auth::id());
        $enriched = [];
        foreach ($all as $b) {
            $b['book'] = Book::find($b['book_id']);
            $enriched[] = $b;
        }

        usort($enriched, fn($a, $b) => strtotime($b['borrow_date']) <=> strtotime($a['borrow_date']));

        $this->view('user/history', [
            'title'   => 'Borrow History',
            'borrows' => $enriched,
            'layout'  => 'public',
            'current_page' => 'history',
        ]);
    }

    public function dashboard(): void {
        $this->requireAuth();
        require_once APP_PATH . '/Models/User.php';
        require_once APP_PATH . '/Models/Borrow.php';
        require_once APP_PATH . '/Models/Order.php';
        require_once APP_PATH . '/Models/Wishlist.php';

        $user = User::find((int)Auth::id());
        $userId = (int)Auth::id();
        $createdAt = $user['created_at'] ?? null;
        $isNewUser = false;
        if ($createdAt) {
            $isNewUser = (time() - strtotime((string)$createdAt)) < (7 * 24 * 60 * 60);
        }

        $borrows = Borrow::forUser($userId);
        $orders = Order::forUser($userId);
        $wishlistItems = Wishlist::booksForUser($userId);

        $activeLoans = 0;
        foreach ($borrows as $borrow) {
            if (($borrow['status'] ?? '') === 'active') {
                $activeLoans++;
            }
        }

        $this->view('user/dashboard', [
            'title' => 'My Dashboard',
            'layout' => 'public',
            'isNewUser' => $isNewUser,
            'user_avatar' => $user['avatar'] ?? null,
            'member_since' => $createdAt,
            'total_borrowed' => count($borrows),
            'active_loans' => $activeLoans,
            'wishlist_count' => count($wishlistItems),
            'pending_orders' => $this->countPendingOrders($orders),
            'recent_activity' => $this->buildRecentActivity($borrows, $orders),
            'current_page' => 'dashboard',
        ]);
    }

    public function reviews(): void {
        $this->requireAuth();
        require_once APP_PATH . '/Models/Review.php';

        $reviews = Review::forUser((int)Auth::id());
        $averageRating = !empty($reviews)
            ? round(array_sum(array_map(static fn(array $review): float => (float)($review['rating'] ?? 0), $reviews)) / count($reviews), 1)
            : 0.0;

        $this->view('user/my-reviews', [
            'title' => 'My Reviews',
            'reviews' => $reviews,
            'reviewCount' => count($reviews),
            'averageRating' => $averageRating,
            'layout' => 'public',
            'current_page' => 'reviews',
        ]);
    }

    private function countPendingOrders(array $orders): int {
        $pending = 0;
        foreach ($orders as $order) {
            if (($order['status'] ?? '') === 'pending') {
                $pending++;
            }
        }
        return $pending;
    }

    private function buildRecentActivity(array $borrows, array $orders, int $limit = 5): array {
        $activities = [];

        foreach ($borrows as $borrow) {
            $createdAt = $borrow['created_at'] ?? $borrow['borrow_date'] ?? null;
            if (!$createdAt) {
                continue;
            }

            $status = (string)($borrow['status'] ?? 'active');
            if ($status === 'returned') {
                $label = 'Returned';
            } elseif ($status === 'overdue') {
                $label = 'Borrow overdue';
            } else {
                $label = 'Borrowed';
            }

            $activities[] = [
                'type' => 'borrow',
                'icon' => 'book-open',
                'action' => $label,
                'time' => timeAgo((string)$createdAt),
                'sort_at' => strtotime((string)$createdAt) ?: 0,
            ];
        }

        foreach ($orders as $order) {
            $createdAt = $order['created_at'] ?? null;
            if (!$createdAt) {
                continue;
            }

            $activities[] = [
                'type' => 'order',
                'icon' => 'box',
                'action' => 'Order ' . ucfirst((string)($order['status'] ?? 'pending')),
                'time' => timeAgo((string)$createdAt),
                'sort_at' => strtotime((string)$createdAt) ?: 0,
            ];
        }

        usort($activities, static fn(array $a, array $b): int => $b['sort_at'] <=> $a['sort_at']);

        return array_map(static function (array $activity): array {
            unset($activity['sort_at']);
            return $activity;
        }, array_slice($activities, 0, $limit));
    }

    public function orders(): void {
        $this->requireAuth();
        require_once APP_PATH . '/Models/Order.php';

        $orders = Order::forUser((int)Auth::id());

        $this->view('user/my-orders', [
            'title' => 'My Orders',
            'orders' => $orders,
            'layout' => 'public',
            'current_page' => 'orders',
        ]);
    }

    public function updatePassword(): void {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            setFlash('error', 'Invalid security token request.');
            $this->redirect('user/profile');
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $validator = new Validator($_POST);
        $validator->required('current_password', 'Current password')
                  ->required('new_password', 'New password')
                  ->minLength('new_password', 6, 'New password')
                  ->matches('new_password', 'confirm_password', 'New password');

        if (!$validator->passes()) {
            setFlash('error', $validator->firstError());
            $this->redirect('user/profile');
        }

        if ($newPassword !== $confirmPassword) {
            setFlash('error', 'New password fields do not match.');
            $this->redirect('user/profile');
        }

        require_once APP_PATH . '/Models/User.php';
        $userId = Auth::id();
        $user = User::find($userId);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            setFlash('error', 'Current password is incorrect.');
            $this->redirect('user/profile');
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $updated = User::updatePassword($userId, $hashedPassword);

        if ($updated) {
            setFlash('success', 'Password updated successfully!');
        } else {
            setFlash('error', 'Failed to update password. Please try again.');
        }

        $this->redirect('user/profile');
    }

    public function deleteAccount(): void {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            setFlash('error', 'Invalid security token request.');
            $this->redirect('user/profile');
        }

        require_once APP_PATH . '/Models/User.php';
        $userId = Auth::id();

        if (User::deleteWithRelations($userId)) {
            Auth::logout();
            setFlash('success', 'Your account has been deleted successfully.');
            $this->redirect('home');
        }

        setFlash('error', 'Failed to delete account.');
        $this->redirect('user/profile');
    }

    public function logoutAllSessions(): void {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            setFlash('error', 'Invalid security token request.');
            $this->redirect('user/profile');
        }

        if (Auth::logoutAllSessions()) {
            setFlash('success', 'You have been logged out from all sessions.');
            $this->redirect('login');
        }

        setFlash('error', 'Unable to sign out from all sessions.');
        $this->redirect('user/profile');
    }
}
