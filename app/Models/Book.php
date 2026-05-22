<?php

class Book {

    private static function db(): PDO {
        return Database::getInstance();
    }

    public static function all(): array {
        $stmt = self::db()->query("SELECT * FROM books ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $stmt = self::db()->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch();
        return $book ?: null;
    }

    public static function findById(int $id): ?array {
        return self::find($id);
    }

    public static function featured(): array {
        $stmt = self::db()->query("SELECT * FROM books WHERE featured = 1 ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public static function byCategory(int $categoryId): array {
        $stmt = self::db()->prepare("SELECT * FROM books WHERE category_id = ? ORDER BY id DESC");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }

    public static function search(string $query): array {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $books = array_map([self::class, 'normalise'], self::all());
        $terms = preg_split('/\s+/', mb_strtolower($query)) ?: [];
        $needle = mb_strtolower($query);

        foreach ($books as &$book) {
            $haystackTitle = mb_strtolower((string)($book['title'] ?? ''));
            $haystackAuthor = mb_strtolower((string)($book['author'] ?? ''));
            $haystackIsbn = mb_strtolower((string)($book['isbn'] ?? ''));
            $haystackDesc = mb_strtolower((string)($book['description'] ?? ''));

            $score = 0;
            if ($haystackTitle === $needle) $score += 100;
            if ($haystackAuthor === $needle) $score += 90;
            if ($haystackIsbn === $needle) $score += 90;
            // Compatibility: avoid PHP 8 only functions
            if ($needle !== '' && mb_strpos($haystackTitle, $needle) === 0) $score += 70;
            if ($needle !== '' && mb_strpos($haystackAuthor, $needle) === 0) $score += 60;
            if ($needle !== '' && mb_strpos($haystackTitle, $needle) !== false) $score += 40;
            if ($needle !== '' && mb_strpos($haystackAuthor, $needle) !== false) $score += 30;
            if ($needle !== '' && mb_strpos($haystackIsbn, $needle) !== false) $score += 35;
            if ($needle !== '' && mb_strpos($haystackDesc, $needle) !== false) $score += 10;

            foreach ($terms as $term) {
                if ($term === '') continue;
                if (mb_strpos($haystackTitle, $term) !== false) $score += 15;
                if (mb_strpos($haystackAuthor, $term) !== false) $score += 12;
                if (mb_strpos($haystackIsbn, $term) !== false) $score += 8;
                if (mb_strpos($haystackDesc, $term) !== false) $score += 4;
            }

            $book['_score'] = $score;
        }
        unset($book);

        $matches = array_values(array_filter($books, fn($book) => ($book['_score'] ?? 0) > 0));
        usort($matches, function ($a, $b) {
            return ($b['_score'] <=> $a['_score']) ?: ((int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0));
        });

        return array_slice($matches, 0, 50);
    }

    public static function count(): int {
        $stmt = self::db()->query("SELECT COUNT(*) AS total FROM books");
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public static function available(): array {
        $stmt = self::db()->query("SELECT * FROM books WHERE available > 0 ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public static function paginate(array $books, int $page = 1, int $perPage = 12): array {
        $total = count($books);
        $pages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        return [
            'data'    => array_slice(array_values($books), $offset, $perPage),
            'total'   => $total,
            'page'    => $page,
            'pages'   => $pages,
            'perPage' => $perPage,
        ];
    }

    /**
     * Returns the correct cover column name for this DB instance.
     * Original schema uses `cover`; the migration adds `cover_image`.
     * We prefer `cover_image` if it exists, otherwise fall back to `cover`.
     */
    public static function coverColumn(): string {
        return Database::columnExists('books', 'cover_image') ? 'cover_image' : 'cover';
    }

    /**
     * Returns the pdf column name, or null if the column does not exist yet.
     */
    public static function pdfColumn(): ?string {
        return Database::columnExists('books', 'pdf_file') ? 'pdf_file' : null;
    }

    /**
     * Normalises a book row so callers always get `cover_image` and `pdf_file`
     * keys regardless of which DB schema is in use.
     */
    public static function normalise(array $book): array {
        if (!isset($book['cover_image']) && isset($book['cover'])) {
            $book['cover_image'] = $book['cover'];
        }
        if (!isset($book['pdf_file'])) {
            $book['pdf_file'] = null;
        }
        return $book;
    }

    public static function create(array $data): int {
        $copies     = max(1, (int)($data['copies'] ?? 1));
        $available  = isset($data['available']) ? max(1, (int)$data['available']) : $copies;
        $categoryId = $data['category_id'] ?? null;
        $categoryId = ($categoryId === '' || $categoryId === null) ? null : (int)$categoryId;

        $coverCol = self::coverColumn();
        $pdfCol   = self::pdfColumn();

        $columns = ['title', 'author', 'category_id', 'isbn', 'description',
            'pages', 'year', 'publisher', 'rating', 'copies', 'available', 'featured',
            $coverCol];
        if ($pdfCol !== null) {
            $columns[] = $pdfCol;
        }
        if (Database::columnExists('books', 'price')) {
            $columns[] = 'price';
        }
        if (Database::columnExists('books', 'format')) {
            $columns[] = 'format';
        }
        if (Database::columnExists('books', 'for_sale')) {
            $columns[] = 'for_sale';
        }
        if (Database::columnExists('books', 'for_borrow')) {
            $columns[] = 'for_borrow';
        }

        $coverValue = $data['cover_image'] ?? $data['cover'] ?? null;
        $pdfValue   = $data['pdf_file'] ?? null;
        $price      = max(0, (float)($data['price'] ?? 0));
        $format     = in_array($data['format'] ?? 'written', ['written', 'audio', 'both'], true) ? $data['format'] : 'written';
        $forSale    = (int)(isset($data['for_sale']) ? $data['for_sale'] : 1);
        $forBorrow  = (int)(isset($data['for_borrow']) ? $data['for_borrow'] : 1);

        $columnValues = [
            'title'       => trim($data['title'] ?? ''),
            'author'      => trim($data['author'] ?? ''),
            'category_id' => $categoryId,
            'isbn'        => trim($data['isbn'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'cover'       => $coverValue,
            'cover_image' => $coverValue,
            'pdf_file'    => $pdfValue,
            'pages'       => (int)($data['pages'] ?? 0),
            'year'        => (int)($data['year'] ?? 0) ?: null,
            'publisher'   => trim($data['publisher'] ?? ''),
            'rating'      => (float)($data['rating'] ?? 0),
            'copies'      => $copies,
            'available'   => max(1, min($available, $copies)),
            'featured'    => (int)($data['featured'] ?? 0),
            'price'       => round($price, 2),
            'format'      => $format,
            'for_sale'    => $forSale,
            'for_borrow'  => $forBorrow,
        ];

        $values       = array_map(fn($col) => $columnValues[$col] ?? null, $columns);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        $stmt = self::db()->prepare(
            "INSERT INTO books (" . implode(', ', $columns) . ") VALUES ({$placeholders})"
        );
        $stmt->execute($values);

        return (int)self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $coverCol = self::coverColumn();
        $pdfCol   = self::pdfColumn();

        $allowed = ['title', 'author', 'category_id', 'isbn', 'description',
            'pages', 'year', 'publisher', 'rating', 'copies', 'available', 'featured',
            $coverCol];
        if ($pdfCol !== null) {
            $allowed[] = $pdfCol;
        }
        if (Database::columnExists('books', 'price')) {
            $allowed[] = 'price';
        }
        if (Database::columnExists('books', 'format')) {
            $allowed[] = 'format';
        }
        if (Database::columnExists('books', 'for_sale')) {
            $allowed[] = 'for_sale';
        }
        if (Database::columnExists('books', 'for_borrow')) {
            $allowed[] = 'for_borrow';
        }

        if (array_key_exists('copies', $data) && !array_key_exists('available', $data)) {
            $current = self::find($id);
            if ($current) {
                $data['available'] = min((int)($current['available'] ?? 0), (int)$data['copies']);
            }
        }

        // Map caller keys to whatever column name this DB uses
        if (array_key_exists('cover_image', $data)) {
            $data[$coverCol] = $data['cover_image'];
        }
        if ($pdfCol !== null && array_key_exists('pdf_file', $data)) {
            $data[$pdfCol] = $data['pdf_file'];
        }

        // Validate and normalize price
        if (array_key_exists('price', $data)) {
            $data['price'] = round(max(0, (float)($data['price'] ?? 0)), 2);
        }

        // Validate format
        if (array_key_exists('format', $data)) {
            if (!in_array($data['format'], ['written', 'audio', 'both'], true)) {
                $data['format'] = 'written';
            }
        }

        $fields = [];
        $values = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field] === '' ? null : $data[$field];
            }
        }

        if (empty($fields)) return false;

        $values[] = $id;
        $stmt = self::db()->prepare("UPDATE books SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete(int $id): bool {
        $stmt = self::db()->prepare("DELETE FROM books WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function adjustAvailable(int $id, int $delta): bool {
        logDebug('Book::adjustAvailable() called', [
            'book_id' => $id,
            'delta' => $delta,
        ], 'books');

        try {
            $stmt = self::db()->prepare(
                "UPDATE books SET available = LEAST(GREATEST(available + ?, 0), copies) WHERE id = ?"
            );

            $result = $stmt->execute([$delta, $id]);

            if (!$result) {
                logError('Book availability update failed', null, [
                    'book_id' => $id,
                    'delta' => $delta,
                    'error_info' => $stmt->errorInfo(),
                ], 'books');
                return false;
            }

            logDebug('Book availability updated successfully', [
                'book_id' => $id,
                'delta' => $delta,
                'rows_affected' => $stmt->rowCount(),
            ], 'books');

            return true;
        } catch (Throwable $e) {
            logError('Book::adjustAvailable() failed', $e, [
                'book_id' => $id,
                'delta' => $delta,
            ], 'books');
            throw $e;
        }
    }

    public static function updateRating(int $id, float $rating): bool {
        $stmt = self::db()->prepare("UPDATE books SET rating = ? WHERE id = ?");
        return $stmt->execute([round(max(0, min(5, $rating)), 1), $id]);
    }
}