<?php

class BorrowController extends Controller {

    public function request(): void {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }
        require_once APP_PATH . '/Models/Book.php';
        require_once APP_PATH . '/Models/Borrow.php';

        $bookId = (int)($_POST['book_id'] ?? 0);
        if ($bookId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid book.'], 422);
        }

        $book = Book::find($bookId);
        if (!$book || (int)$book['available'] <= 0) {
            $this->json(['success' => false, 'message' => 'Book is not available.'], 409);
        }

        $borrowDate = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime('+30 days'));
        Borrow::create(Auth::id(), $bookId, 'active', $borrowDate, $dueDate);
        Book::adjustAvailable($bookId, -1);

        $this->json([
            'success' => true,
            'message' => 'Book borrowed successfully! Due date: ' . date('M d, Y', strtotime($dueDate)),
        ]);
    }

    public function returnBook(): void {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }
        require_once APP_PATH . '/Models/Borrow.php';
        require_once APP_PATH . '/Models/Book.php';

        $borrowId = (int)($_POST['borrow_id'] ?? 0);
        if ($borrowId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid borrow.'], 422);
        }

        $borrow = Borrow::find($borrowId);
        if (!$borrow || (int)$borrow['user_id'] !== Auth::id()) {
            $this->json(['success' => false, 'message' => 'Borrow not found.'], 404);
        }

        Borrow::updateStatus($borrowId, 'returned', date('Y-m-d'));
        Book::adjustAvailable((int)$borrow['book_id'], 1);

        $this->json([
            'success' => true,
            'message' => 'Book returned successfully. Thank you!',
        ]);
    }

    public function reserve(): void {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 422);
        }
        require_once APP_PATH . '/Models/Borrow.php';
        require_once APP_PATH . '/Models/Book.php';

        $bookId = (int)($_POST['book_id'] ?? 0);
        if ($bookId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid book.'], 422);
        }

        Borrow::create(Auth::id(), $bookId, 'reserved', date('Y-m-d'), date('Y-m-d', strtotime('+30 days')));

        try {
            require_once APP_PATH . '/Models/Notification.php';
            $adminId = 1;
            $book = Book::find($bookId);
            $title = $book['title'] ?? 'Unknown Title';
            $name = Auth::user()['name'] ?? 'Unknown User';
            Notification::create(
                $adminId,
                "📚 New borrow request! User '{$name}' requested to borrow: '{$title}'.",
                'borrow_request'
            );
        } catch (Throwable $e) {
            error_log('Failed to send borrow request notification: ' . $e->getMessage());
        }

        $this->json([
            'success' => true,
            'message' => 'Book reserved! We\'ll notify you when it\'s available.',
        ]);
    }

    /* ═══════════════════════════════════════════════════════
       ADMIN METHODS (المطلوبة لـ لوحة التحكم والجدول لتجنب الـ 403 والـ HTML)
       ═══════════════════════════════════════════════════════ */

    public function approve(): void {
        // تأكيد الصلاحيات والـ CSRF
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Security token invalid.'], 403);
        }

        require_once APP_PATH . '/Models/Borrow.php';
        require_once APP_PATH . '/Models/Book.php';

        $id = (int)($_POST['id'] ?? 0);
        $borrow = Borrow::find($id);

        if (!$borrow) {
            $this->json(['success' => false, 'message' => 'Record not found.'], 404);
        }

        // تحديث حالة الحجز التمهيدي ليصبح استعارة نشطة (Active)
        Borrow::updateStatus($id, 'active');
        // تقليل الكمية المتاحة من الكتاب بالمستودع بمقدار 1
        Book::adjustAvailable((int)$borrow['book_id'], -1);

        $this->json(['success' => true, 'message' => 'Reservation approved and active now!']);
    }

    public function reject(): void {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Security token invalid.'], 403);
        }

        require_once APP_PATH . '/Models/Borrow.php';

        $id = (int)($_POST['id'] ?? 0);
        $borrow = Borrow::find($id);

        if (!$borrow) {
            $this->json(['success' => false, 'message' => 'Record not found.'], 404);
        }

        Borrow::updateStatus($id, 'rejected');

        $this->json(['success' => true, 'message' => 'Reservation has been rejected.']);
    }

    public function adminReturn(): void {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Security token invalid.'], 403);
        }

        require_once APP_PATH . '/Models/Borrow.php';
        require_once APP_PATH . '/Models/Book.php';

        $id = (int)($_POST['id'] ?? 0);
        $borrow = Borrow::find($id);

        if (!$borrow) {
            $this->json(['success' => false, 'message' => 'Record not found.'], 404);
        }

        Borrow::updateStatus($id, 'returned', date('Y-m-d'));
        Book::adjustAvailable((int)$borrow['book_id'], 1);

        $this->json(['success' => true, 'message' => 'Book marked as returned successfully.']);
    }
}