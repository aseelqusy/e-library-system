<?php

class HomeController extends Controller {

    public function index(): void {
        require_once APP_PATH . '/Models/Book.php';
        require_once APP_PATH . '/Models/Category.php';
        require_once APP_PATH . '/Models/Borrow.php';
        require_once APP_PATH . '/Models/User.php';

        $featuredBooks = array_values(Book::featured());
        // Ensure each book has normalized cover/pdf keys for the views
        $featuredBooks = array_map(fn($b) => Book::normalise($b), $featuredBooks);
        $quote = null;
        if (!empty($featuredBooks)) {
            $spotlight = $featuredBooks[0];
            $text = trim($spotlight['description'] ?? '') ?: ($spotlight['title'] ?? '');
            $author = trim($spotlight['author'] ?? '');
            if ($text !== '') {
                $quote = ['text' => $text, 'author' => $author];
            }
        }

        $this->view('home/landing', [
            'title'         => 'Welcome to ' . APP_NAME,
            'featured'      => $featuredBooks,
            'totalBooks'    => Book::count(),
            'totalUsers'    => User::count(),
            'borrowedToday' => Borrow::todayCount(),
            'categories'    => Category::all(),
            'quote'         => $quote,
            'layout'        => 'public',
        ]);
    }
}
