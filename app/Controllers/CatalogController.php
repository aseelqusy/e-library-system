<?php

class CatalogController extends Controller {

    public function browse(): void {
        require_once APP_PATH . '/Models/Book.php';
        require_once APP_PATH . '/Models/Category.php';

        $books      = Book::all();
        $category   = $_GET['category'] ?? '';
        $sort       = $_GET['sort'] ?? 'newest';
        $q          = $_GET['q'] ?? '';
        $categories = Category::all();
        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[$cat['id']] = $cat;
        }

        if ($category) {
            $books = array_filter($books, fn($b) => $b['category_id'] == $category);
        }
        if ($q) {
            $books = Book::search($q);
        }

        // Sort
        $booksArr = array_values($books);
        switch ($sort) {
            case 'title':   usort($booksArr, fn($a,$b) => strcmp($a['title'], $b['title'])); break;
            case 'rating':  usort($booksArr, fn($a,$b) => $b['rating'] <=> $a['rating']); break;
            case 'year':    usort($booksArr, fn($a,$b) => $b['year'] <=> $a['year']); break;
            default:        usort($booksArr, fn($a,$b) => $b['id'] <=> $a['id']); break;
        }

        $this->view('catalog/browse', [
            'title'      => 'Browse Catalog',
            'books'      => $booksArr,
            'categories' => $categoryMap,
            'filters'    => ['category' => $category, 'sort' => $sort, 'q' => $q],
            'layout'     => 'public',
        ]);
    }

    public function bookDetails(string $id): void {
        require_once APP_PATH . '/Models/Book.php';
        require_once APP_PATH . '/Models/Category.php';
        require_once APP_PATH . '/Models/Review.php';

        if (!ctype_digit($id)) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Book Not Found']);
            return;
        }

        $bookId = (int)$id;
        if ($bookId <= 0) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Book Not Found']);
            return;
        }

        $book = Book::findById($bookId);
        if (!$book) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Book Not Found']);
            return;
        }

        $book = Book::normalise($book);

        $category = !empty($book['category_id']) ? Category::find((int)$book['category_id']) : null;
        $reviews  = Review::forBook($bookId);
        $allBooks = array_values(Book::all());
        $similar  = array_values(array_filter($allBooks, fn($b) => (int)$b['id'] !== $bookId));
        $similar  = array_slice($similar, 0, 4);

        $this->view('catalog/book-details', [
            'title'    => $book['title'],
            'book'     => $book,
            'category' => $category,
            'reviews'  => $reviews,
            'similar'  => $similar,
            'layout'   => 'public',
        ]);
    }

    public function categories(): void {
        require_once APP_PATH . '/Models/Category.php';
        require_once APP_PATH . '/Models/Book.php';

        $categories = Category::all();
        $counts = [];
        foreach ($categories as $cat) {
            $counts[$cat['id']] = count(Book::byCategory($cat['id']));
        }

        $this->view('catalog/categories', [
            'title'      => 'Categories',
            'categories' => $categories,
            'counts'     => $counts,
            'layout'     => 'public',
        ]);
    }

    public function search(): void {
        require_once APP_PATH . '/Models/Book.php';

        $q     = $_GET['q'] ?? '';
        $books = $q ? Book::search($q) : [];

        $this->view('catalog/search', [
            'title'  => 'Search Results',
            'books'  => array_values($books),
            'query'  => $q,
            'layout' => 'public',
        ]);
    }
}