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
}
