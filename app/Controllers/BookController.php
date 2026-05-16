<?php

class BookController extends Controller {

    public function show(string $id): void {
        require_once APP_PATH . '/Models/Book.php';
        $book = Book::find((int)$id);
        if (!$book) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Book Not Found']);
            return;
        }
        $this->json($book);
    }
}
