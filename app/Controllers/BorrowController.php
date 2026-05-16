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

        $bookId = (int)($_POST['book_id'] ?? 0);
        if ($bookId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid book.'], 422);
        }

        Borrow::create(Auth::id(), $bookId, 'reserved', date('Y-m-d'), date('Y-m-d', strtotime('+30 days')));

        $this->json([
            'success' => true,
            'message' => 'Book reserved! We\'ll notify you when it\'s available.',
        ]);
    }
}
