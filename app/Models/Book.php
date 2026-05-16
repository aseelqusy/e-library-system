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
        $q = '%' . $query . '%';
        $stmt = self::db()->prepare(
            "SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ? ORDER BY id DESC"
        );
        $stmt->execute([$q, $q, $q]);
        return $stmt->fetchAll();
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
        $copies     = max(0, (int)($data['copies'] ?? 1));
        $available  = isset($data['available']) ? (int)$data['available'] : $copies;
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

        $coverValue = $data['cover_image'] ?? $data['cover'] ?? null;
        $pdfValue   = $data['pdf_file'] ?? null;

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
            'available'   => max(0, min($available, $copies)),
            'featured'    => (int)($data['featured'] ?? 0),
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
        $stmt = self::db()->prepare(
            "UPDATE books SET available = LEAST(GREATEST(available + ?, 0), copies) WHERE id = ?"
        );
        return $stmt->execute([$delta, $id]);
    }
}