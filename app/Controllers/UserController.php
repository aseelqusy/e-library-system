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

        Auth::updateUser(Auth::id(), [
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
        ]);
    }

    public function dashboard(): void {
        $this->requireAuth();

        $this->view('user/dashboard', [
            'title'  => 'My Dashboard',
            'layout' => 'public',
        ]);
    }

    public function updatePassword(): void {
        // فرض تسجيل الدخول أولاً قبل أي إجراء
        $this->requireAuth();

        // التحقق من توكن الحماية باستخدام الدالة المدمجة بمشروعك
        if (!$this->validateCsrf()) {
            setFlash('error', 'Invalid security token request.');
            $this->redirect('user/profile');
            return;
        }

        // استقبال البيانات القادمة من الـ Form
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';

        // جلب بيانات المستخدم الحالي عبر الموديل والمُعرّف المخزن في الجلسة Auth::id()
        require_once APP_PATH . '/Models/User.php';
        $userId = Auth::id();
        $user = User::find($userId);

        // التحقق من صحة كلمة المرور الحالية المخزنة في الـ DB (bcrypt)
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            setFlash('error', 'Current password is incorrect.');
            $this->redirect('user/profile');
            return;
        }

        // تشفير كلمة المرور الجديدة وتحديثها في قاعدة البيانات
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // استخدام دالة التحديث المدعومة في نظام الـ Auth الخاص بمشروعك
        $updated = Auth::updateUser($userId, [
            'password' => $hashedPassword
        ]);

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
            return;
        }

        require_once APP_PATH . '/Models/User.php';
        $userId = Auth::id();

        // تحديث حالة الحساب إلى غير نشط (is_active = 0) بناءً على الـ Schema الخاصة بك
        $deleted = Auth::updateUser($userId, [
            'is_active' => 0
        ]);

        if ($deleted) {
            // تسجيل الخروج التلقائي بعد تعطيل الحساب
            Auth::logout();

            // بدء جلسة جديدة لإظهار توبست النجاح على الصفحة الرئيسية
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            setFlash('success', 'Your account has been deleted successfully.');
            $this->redirect('home');
        } else {
            setFlash('error', 'Failed to delete account.');
            $this->redirect('user/profile');
        }
    }
}
