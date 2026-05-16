<?php

class Category {

    private static function db(): PDO {
        return Database::getInstance();
    }

    private static function slugify(string $text): string {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    private static function uniqueSlug(string $name, ?int $ignoreId = null): string {
        $base = self::slugify($name);
        $slug = $base;
        $i = 1;

        while (self::slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private static function slugExists(string $slug, ?int $ignoreId = null): bool {
        $sql = "SELECT id FROM categories WHERE slug = ?";
        $params = [$slug];
        if ($ignoreId !== null) {
            $sql .= " AND id != ?";
            $params[] = $ignoreId;
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    public static function all(): array {
        $stmt = self::db()->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $stmt = self::db()->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $cat = $stmt->fetch();
        return $cat ?: null;
    }

    public static function findBySlug(string $slug): ?array {
        $stmt = self::db()->prepare("SELECT * FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        $cat = $stmt->fetch();
        return $cat ?: null;
    }

    public static function count(): int {
        $stmt = self::db()->query("SELECT COUNT(*) AS total FROM categories");
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public static function create(array $data): int {
        $name = trim($data['name'] ?? '');
        $slug = self::uniqueSlug($name);

        $stmt = self::db()->prepare(
            "INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $name,
            $slug,
            trim($data['description'] ?? ''),
            trim($data['icon'] ?? ''),
        ]);

        return (int)self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $name = trim($data['name'] ?? '');
        $slug = $name ? self::uniqueSlug($name, $id) : null;

        $stmt = self::db()->prepare(
            "UPDATE categories SET name = ?, slug = ?, description = ?, icon = ? WHERE id = ?"
        );
        return $stmt->execute([
            $name,
            $slug,
            trim($data['description'] ?? ''),
            trim($data['icon'] ?? ''),
            $id,
        ]);
    }

    public static function delete(int $id): bool {
        $stmt = self::db()->prepare("UPDATE books SET category_id = NULL WHERE category_id = ?");
        $stmt->execute([$id]);

        $stmt = self::db()->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function countsWithBooks(): array {
        $stmt = self::db()->query(
            "SELECT c.name, COUNT(b.id) AS total
             FROM categories c
             LEFT JOIN books b ON b.category_id = c.id
             GROUP BY c.id
             ORDER BY c.name ASC"
        );
        return $stmt->fetchAll();
    }
}
