<?php

class AdminController extends Controller {

    public function __construct() {
        // All admin methods need admin auth
    }

    private function ensureAdmin(): void {
        $this->requireAuth();
        $this->requireAdmin();
    }

    private function countByDateRange(string $table, string $column, string $start, string $end): int {
        $stmt = Database::getInstance()->prepare(
            "SELECT COUNT(*) AS total FROM {$table} WHERE {$column} >= ? AND {$column} < ?"
        );
        $stmt->execute([$start, $end]);
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public function dashboard(): void {
        $this->ensureAdmin();
        require_once APP_PATH . '/Models/Book.php';
        require_once APP_PATH . '/Models/Borrow.php';
        require_once APP_PATH . '/Models/User.php';
        require_once APP_PATH . '/Models/Category.php';
        require_once APP_PATH . '/Models/Review.php';
        require_once APP_PATH . '/Models/Wishlist.php';

        $startThis = (new DateTimeImmutable('first day of this month'))->format('Y-m-d');
        $startNext = (new DateTimeImmutable('first day of next month'))->format('Y-m-d');
        $startLast = (new DateTimeImmutable('first day of last month'))->format('Y-m-d');

        $booksThisMonth   = $this->countByDateRange('books',   'created_at',  $startThis, $startNext);
        $booksLastMonth   = $this->countByDateRange('books',   'created_at',  $startLast, $startThis);
        $usersThisMonth   = $this->countByDateRange('users',   'created_at',  $startThis, $startNext);
        $usersLastMonth   = $this->countByDateRange('users',   'created_at',  $startLast, $startThis);
        $borrowsThisMonth = $this->countByDateRange('borrows', 'borrow_date', $startThis, $startNext);
        $borrowsLastMonth = $this->countByDateRange('borrows', 'borrow_date', $startLast, $startThis);

        $borrowSeries  = Borrow::countByMonth(12);
        $categorySeries = Category::countsWithBooks();
        $recentBorrows = Borrow::recentWithDetails(6);

        $this->view('admin/dashboard', [
            'title'            => 'Admin Dashboard',
            'totalBooks'       => Book::count(),
            'totalUsers'       => User::count(),
            'activeBorrows'    => count(Borrow::active()),
            'overdueCount'     => count(Borrow::overdue()),
            'totalCategories'  => Category::count(),
            'totalReviews'     => Review::count(),
            'totalWishlists'   => Wishlist::count(),
            'totalBorrows'     => Borrow::count(),
            'booksThisMonth'   => $booksThisMonth,
            'booksLastMonth'   => $booksLastMonth,
            'usersThisMonth'   => $usersThisMonth,
            'usersLastMonth'   => $usersLastMonth,
            'borrowsThisMonth' => $borrowsThisMonth,
            'borrowsLastMonth' => $borrowsLastMonth,
            'borrowChart'      => $borrowSeries,
            'categoryChart'    => $categorySeries,
            'recentBorrows'    => $recentBorrows,
            'layout'           => 'admin',
        ]);
    }

    public function books(): void {
        $this->ensureAdmin();
        require_once APP_PATH . '/Models/Book.php';
        require_once APP_PATH . '/Models/Category.php';

        $this->view('admin/books', [
            'title'      => 'Manage Books',
            'books'      => Book::all(),
            'categories' => Category::all(),
            'layout'     => 'admin',
        ]);
    }

    private function saveUpload(?array $file, string $type): array {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['path' => null];
        }

        if (!empty($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload failed.'];
        }

        $isImage  = $type === 'image';
        $maxBytes = $isImage ? UPLOAD_MAX_IMAGE_BYTES : UPLOAD_MAX_PDF_BYTES;
        if (($file['size'] ?? 0) > $maxBytes) {
            return ['error' => $isImage ? 'Cover image is too large.' : 'PDF file is too large.'];
        }

        $allowedMimes = $isImage
            ? ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
            : ['application/pdf'];
        $allowedExts  = $isImage
            ? ['jpg', 'jpeg', 'png', 'gif', 'webp']
            : ['pdf'];

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) {
            return ['error' => $isImage ? 'Invalid image format.' : 'Only PDF files are allowed.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowedMimes, true)) {
            return ['error' => $isImage ? 'Invalid image type.' : 'Invalid PDF type.'];
        }

        $subDir = $isImage ? 'images' : 'pdfs';
        $dir    = UPLOAD_DIR . '/' . $subDir;
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            return ['error' => 'Upload directory is not writable.'];
        }

        $filename = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
        $target   = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return ['error' => 'Failed to save uploaded file.'];
        }

        return ['path' => 'uploads/' . $subDir . '/' . $filename];
    }

    private function deleteUpload(?string $path): void {
        if (!$path) return;
        $fullPath = PUBLIC_PATH . '/' . ltrim($path, '/');
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    public function storeBook(): void {
        $this->ensureAdmin();
        if (!$this->validateCsrf()) {
            setFlash('error', 'Invalid request.');
            $this->redirect('admin/books');
        }

        require_once APP_PATH . '/Models/Book.php';

        $data = [
            'title'       => trim($_POST['title']       ?? ''),
            'author'      => trim($_POST['author']      ?? ''),
            'isbn'        => trim($_POST['isbn']        ?? ''),
            'category_id' => $_POST['category_id']     ?? null,
            'year'        => $_POST['year']             ?? null,
            'pages'       => $_POST['pages']            ?? null,
            'copies'      => $_POST['copies']           ?? 1,
            'publisher'   => trim($_POST['publisher']   ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ];

        if ($data['title'] === '' || $data['author'] === '' || $data['isbn'] === '') {
            setFlash('error', 'Title, author, and ISBN are required.');
            $this->redirect('admin/books');
        }

        $coverUpload = $this->saveUpload($_FILES['cover_image'] ?? null, 'image');
        if (!empty($coverUpload['error'])) {
            setFlash('error', $coverUpload['error']);
            $this->redirect('admin/books');
        }

        $pdfUpload = $this->saveUpload($_FILES['pdf_file'] ?? null, 'pdf');
        if (!empty($pdfUpload['error'])) {
            if (!empty($coverUpload['path'])) $this->deleteUpload($coverUpload['path']);
            setFlash('error', $pdfUpload['error']);
            $this->redirect('admin/books');
        }

        $data['cover_image'] = $coverUpload['path'];
        $data['pdf_file']    = $pdfUpload['path'];

        try {
            Book::create($data);
            setFlash('success', 'Book added successfully.');
        } catch (Throwable $e) {
            if (!empty($data['cover_image'])) $this->deleteUpload($data['cover_image']);
            if (!empty($data['pdf_file']))    $this->deleteUpload($data['pdf_file']);
            error_log('Book create failed: ' . $e->getMessage());
            setFlash('error', 'Failed to add book: ' . $e->getMessage());
        }
        $this->redirect('admin/books');
    }

    public function updateBook(): void {
        $this->ensureAdmin();
        if (!$this->validateCsrf()) {
            setFlash('error', 'Invalid request.');
            $this->redirect('admin/books');
        }

        require_once APP_PATH . '/Models/Book.php';

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            setFlash('error', 'Invalid book selection.');
            $this->redirect('admin/books');
        }

        $book = Book::find($id);
        if (!$book) {
            setFlash('error', 'Book not found.');
            $this->redirect('admin/books');
        }
        $book = Book::normalise($book);

        $data = [
            'title'       => trim($_POST['title']       ?? ''),
            'author'      => trim($_POST['author']      ?? ''),
            'isbn'        => trim($_POST['isbn']        ?? ''),
            'category_id' => $_POST['category_id']     ?? null,
            'year'        => $_POST['year']             ?? null,
            'pages'       => $_POST['pages']            ?? null,
            'copies'      => $_POST['copies']           ?? null,
            'publisher'   => trim($_POST['publisher']   ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ];

        $coverUpload = $this->saveUpload($_FILES['cover_image'] ?? null, 'image');
        if (!empty($coverUpload['error'])) {
            setFlash('error', $coverUpload['error']);
            $this->redirect('admin/books');
        }
        if (!empty($coverUpload['path'])) {
            $data['cover_image'] = $coverUpload['path'];
            $this->deleteUpload($book['cover_image'] ?? null);
        }

        $pdfUpload = $this->saveUpload($_FILES['pdf_file'] ?? null, 'pdf');
        if (!empty($pdfUpload['error'])) {
            if (!empty($coverUpload['path'])) $this->deleteUpload($coverUpload['path']);
            setFlash('error', $pdfUpload['error']);
            $this->redirect('admin/books');
        }
        if (!empty($pdfUpload['path'])) {
            $data['pdf_file'] = $pdfUpload['path'];
            $this->deleteUpload($book['pdf_file'] ?? null);
        }

        try {
            Book::update($id, $data);
            setFlash('success', 'Book updated successfully.');
        } catch (Throwable $e) {
            error_log('Book update failed: ' . $e->getMessage());
            setFlash('error', 'Failed to update book: ' . $e->getMessage());
        }
        $this->redirect('admin/books');
    }

    public function deleteBook(): void {
        $this->ensureAdmin();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/Book.php';
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid book.'], 422);
        }

        $book = Book::find($id);
        if (!$book) {
            $this->json(['success' => false, 'message' => 'Book not found.'], 404);
        }
        $book = Book::normalise($book);

        try {
            Book::delete($id);
            $this->deleteUpload($book['cover_image'] ?? null);
            $this->deleteUpload($book['pdf_file']    ?? null);
            $this->json(['success' => true, 'message' => 'Book deleted.']);
        } catch (Throwable $e) {
            error_log('Book delete failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Delete failed.'], 500);
        }
    }

    // ── Users ──────────────────────────────────────────────────────────────

    public function users(): void {
        $this->ensureAdmin();
        require_once APP_PATH . '/Models/User.php';

        $this->view('admin/users', [
            'title'  => 'Manage Users',
            'users'  => User::all(),
            'layout' => 'admin',
        ]);
    }

    public function updateUserRole(): void {
        $this->ensureAdmin();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/User.php';
        $id   = (int)($_POST['id']   ?? 0);
        $role = $_POST['role']        ?? '';

        if ($id <= 0 || !in_array($role, ['admin', 'member'], true)) {
            $this->json(['success' => false, 'message' => 'Invalid user or role.'], 422);
        }

        // Prevent removing your own admin role
        if (Auth::id() === $id && $role !== 'admin') {
            $this->json(['success' => false, 'message' => 'You cannot demote your own account.'], 403);
        }

        try {
            User::updateRole($id, $role);
            $this->json(['success' => true, 'message' => 'Role updated to ' . $role . '.']);
        } catch (Throwable $e) {
            error_log('Role update failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Role update failed.'], 500);
        }
    }

    public function deactivateUser(): void {
        $this->ensureAdmin();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/User.php';
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid user.'], 422);
        }

        if (Auth::id() === $id) {
            $this->json(['success' => false, 'message' => 'You cannot deactivate your own account.'], 403);
        }

        try {
            User::deactivate($id);
            $this->json(['success' => true, 'message' => 'User deactivated.']);
        } catch (Throwable $e) {
            error_log('Deactivation failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Deactivation failed.'], 500);
        }
    }

    public function activateUser(): void {
        $this->ensureAdmin();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/User.php';
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid user.'], 422);
        }

        try {
            User::activate($id);
            $this->json(['success' => true, 'message' => 'User activated.']);
        } catch (Throwable $e) {
            error_log('Activation failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Activation failed.'], 500);
        }
    }

    public function deleteUser(): void {
        $this->ensureAdmin();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/User.php';
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid user.'], 422);
        }

        $user = User::find($id);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found.'], 404);
        }
        if (($user['role'] ?? '') === 'admin') {
            $this->json(['success' => false, 'message' => 'Admin accounts cannot be deleted.'], 403);
        }
        if (Auth::id() === $id) {
            $this->json(['success' => false, 'message' => 'You cannot delete your own account.'], 403);
        }

        if (User::deleteWithRelations($id)) {
            $this->json(['success' => true, 'message' => 'User deleted.']);
        }

        $this->json(['success' => false, 'message' => 'Delete failed.'], 500);
    }

    // ── Borrows ────────────────────────────────────────────────────────────

    public function borrows(): void {
        $this->ensureAdmin();
        require_once APP_PATH . '/Models/Borrow.php';
        require_once APP_PATH . '/Models/Book.php';
        require_once APP_PATH . '/Models/User.php';

        $borrows  = Borrow::all();
        $enriched = [];
        foreach ($borrows as $b) {
            $b['book'] = Book::find($b['book_id']);
            $b['user'] = User::find($b['user_id']);
            $enriched[] = $b;
        }

        $this->view('admin/borrows', [
            'title'   => 'Manage Borrows',
            'borrows' => $enriched,
            'layout'  => 'admin',
        ]);
    }

    public function markBorrowReturned(): void {
        $this->ensureAdmin();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/Borrow.php';
        require_once APP_PATH . '/Models/Book.php';

        $id     = (int)($_POST['id'] ?? 0);
        $borrow = Borrow::find($id);
        if (!$borrow) {
            $this->json(['success' => false, 'message' => 'Borrow not found.'], 404);
        }

        try {
            Borrow::updateStatus($id, 'returned', date('Y-m-d'));
            Book::adjustAvailable((int)$borrow['book_id'], 1);
            $this->json(['success' => true, 'message' => 'Borrow marked as returned.']);
        } catch (Throwable $e) {
            error_log('Mark returned failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Update failed.'], 500);
        }
    }

    public function approveReservation(): void {
        $this->ensureAdmin();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/Borrow.php';
        require_once APP_PATH . '/Models/Book.php';

        $id     = (int)($_POST['id'] ?? 0);
        $borrow = Borrow::find($id);
        if (!$borrow) {
            $this->json(['success' => false, 'message' => 'Borrow not found.'], 404);
        }

        $book = Book::find((int)$borrow['book_id']);
        if (!$book || (int)$book['available'] <= 0) {
            $this->json(['success' => false, 'message' => 'No available copies to approve.'], 409);
        }

        try {
            Borrow::updateStatus(
                $id,
                'active',
                null,
                date('Y-m-d'),
                date('Y-m-d', strtotime('+30 days'))
            );
            Book::adjustAvailable((int)$borrow['book_id'], -1);
            $this->json(['success' => true, 'message' => 'Reservation approved.']);
        } catch (Throwable $e) {
            error_log('Approve reservation failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Approval failed.'], 500);
        }
    }

    public function rejectReservation(): void {
        $this->ensureAdmin();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/Borrow.php';

        $id     = (int)($_POST['id'] ?? 0);
        $borrow = Borrow::find($id);
        if (!$borrow) {
            $this->json(['success' => false, 'message' => 'Borrow not found.'], 404);
        }

        if ($borrow['status'] !== 'reserved') {
            $this->json(['success' => false, 'message' => 'Only reserved borrows can be rejected.'], 422);
        }

        try {
            // Use 'rejected' status (requires upgraded ENUM — see upgrade_migration.sql)
            Borrow::updateStatus($id, 'rejected', date('Y-m-d'));
            $this->json(['success' => true, 'message' => 'Reservation rejected.']);
        } catch (Throwable $e) {
            error_log('Reject reservation failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Rejection failed.'], 500);
        }
    }

    // ── Categories ─────────────────────────────────────────────────────────

    public function categories(): void {
        $this->ensureAdmin();
        require_once APP_PATH . '/Models/Category.php';
        require_once APP_PATH . '/Models/Book.php';

        $categories = Category::all();
        $counts     = [];
        foreach ($categories as $cat) {
            $counts[$cat['id']] = count(Book::byCategory($cat['id']));
        }

        $this->view('admin/categories', [
            'title'      => 'Manage Categories',
            'categories' => $categories,
            'counts'     => $counts,
            'layout'     => 'admin',
        ]);
    }

    public function storeCategory(): void {
        $this->ensureAdmin();
        if (!$this->validateCsrf()) {
            setFlash('error', 'Invalid request.');
            $this->redirect('admin/categories');
        }

        require_once APP_PATH . '/Models/Category.php';

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            setFlash('error', 'Category name is required.');
            $this->redirect('admin/categories');
        }

        try {
            Category::create([
                'name'        => $name,
                'description' => trim($_POST['description'] ?? ''),
                'icon'        => trim($_POST['icon']        ?? ''),
            ]);
            setFlash('success', 'Category created.');
        } catch (Throwable $e) {
            error_log('Category create failed: ' . $e->getMessage());
            setFlash('error', 'Failed to create category.');
        }
        $this->redirect('admin/categories');
    }

    public function updateCategory(): void {
        $this->ensureAdmin();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/Category.php';

        $id   = (int)($_POST['id']   ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($id <= 0 || $name === '') {
            $this->json(['success' => false, 'message' => 'Invalid category.'], 422);
        }

        try {
            Category::update($id, [
                'name'        => $name,
                'description' => trim($_POST['description'] ?? ''),
                'icon'        => trim($_POST['icon']        ?? ''),
            ]);
            $this->json(['success' => true, 'message' => 'Category updated.']);
        } catch (Throwable $e) {
            error_log('Category update failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Update failed.'], 500);
        }
    }

    public function deleteCategory(): void {
        $this->ensureAdmin();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }

        require_once APP_PATH . '/Models/Category.php';

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid category.'], 422);
        }

        try {
            Category::delete($id);
            $this->json(['success' => true, 'message' => 'Category deleted.']);
        } catch (Throwable $e) {
            error_log('Category delete failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Delete failed.'], 500);
        }
    }

    // ── Reports ────────────────────────────────────────────────────────────

    public function reports(): void {
        $this->ensureAdmin();
        require_once APP_PATH . '/Models/Book.php';
        require_once APP_PATH . '/Models/Borrow.php';
        require_once APP_PATH . '/Models/Review.php';

        $reviews   = Review::all();
        $avgRating = count($reviews) > 0
            ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1)
            : 0;

        $this->view('admin/reports', [
            'title'  => 'Reports',
            'stats'  => [
                'total_books'   => Book::count(),
                'total_borrows' => Borrow::count(),
                'avg_rating'    => $avgRating,
                'overdue'       => count(Borrow::overdue()),
            ],
            'layout' => 'admin',
        ]);
    }

    // ── Settings ───────────────────────────────────────────────────────────

    public function settings(): void {
        $this->ensureAdmin();
        $dbConnected = true;
        $dbError     = null;
        $settings    = []; // مصفوفة فارغة لتخزين الإعدادات القادمة من الداتا بيس

        try {
            $db = Database::getInstance();

            // تنفيذ الاستعلام لجلب كل الصفوف من الجدول
            $stmt = $db->query("SELECT * FROM settings");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // تحويل الصفوف إلى مصفوفة يسهل قراءتها في الواجهة (Key => Value)
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }

        } catch (Throwable $e) {
            $dbConnected = false;
            $dbError     = $e->getMessage();
        }

        // تمرير المصفوفة الحية إلى صفحة الواجهة settings.php
        $this->view('admin/settings', [
            'title'       => 'Settings',
            'layout'      => 'admin',
            'dbConnected' => $dbConnected,
            'dbError'     => $dbError,
            'settings'    => $settings, // أضفنا هذا المتغير هنا
        ]);
    }

    public function updateSettings(): void {
        $this->ensureAdmin();
        if (!$this->validateCsrf()) {
            setFlash('error', 'Invalid request.');
            $this->redirect('admin/settings');
        }

        try {
            $db = Database::getInstance();

            // تجهيز استعلام مرن يقوم بالإدخال أو التحديث تلقائياً إذا كان المفتاح موجوداً
            $query = "INSERT INTO settings (setting_key, setting_value) 
                  VALUES (:key, :value) 
                  ON DUPLICATE KEY UPDATE setting_value = :value";
            $stmt = $db->prepare($query);

            // الدوران على كل الحقول المرسلة من واجهة الإعدادات وحفظها
            foreach ($_POST as $key => $value) {
                // تخطي توكن الحماية لأنه ليس جزءاً من إعدادات النظام
                if ($key === 'csrf_token') {
                    continue;
                }

                $stmt->execute([
                    'key'   => $key,
                    'value' => $value
                ]);
            }

            setFlash('success', 'Settings saved successfully.');
        } catch (Throwable $e) {
            setFlash('error', 'Failed to save settings: ' . $e->getMessage());
        }

        $this->redirect('admin/settings');
    }
}
