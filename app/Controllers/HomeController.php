<?php

class HomeController extends Controller {

    public function index(): void {
        require_once APP_PATH . '/Models/Book.php';
        require_once APP_PATH . '/Models/Category.php';
        require_once APP_PATH . '/Models/Borrow.php';
        require_once APP_PATH . '/Models/User.php';
        require_once APP_PATH . '/Models/Activity.php';
        require_once APP_PATH . '/Models/Quote.php';

        $featuredBooks = array_values(Book::featured());
        // Ensure each book has normalized cover/pdf keys for the views
        $featuredBooks = array_map(fn($b) => Book::normalise($b), $featuredBooks);
        $quotes = Quote::active(8);
        if (empty($quotes) && !empty($featuredBooks)) {
            $spotlight = $featuredBooks[0];
            $text = trim($spotlight['description'] ?? '') ?: ($spotlight['title'] ?? '');
            $author = trim($spotlight['author'] ?? '');
            if ($text !== '') {
                $quotes = [[
                    'quote_text' => $text,
                    'quote_author' => $author,
                    'source' => 'Book Spotlight',
                ]];
            }
        }

        $activities = Activity::latest(6);


        $this->view('home/landing', [
            'title'         => 'Welcome to ' . APP_NAME,
            'featured'      => $featuredBooks,
            'totalBooks'    => Book::count(),
            'totalUsers'    => User::count(),
            'borrowedToday' => Borrow::todayCount(),
            'categories'    => Category::all(),
            'quotes'        => $quotes,
            'activities'    => $activities,
            'layout'        => 'public',
        ]);
    }
}
