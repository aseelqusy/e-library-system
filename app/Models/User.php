<?php

/**
 * User Model
 *
 * Schema:
 *   users (id INT PK AUTO_INCREMENT, name VARCHAR(100), email VARCHAR(150) UNIQUE,
 *          password VARCHAR(255), role ENUM('admin','member'), avatar VARCHAR(255),
 *          created_at DATETIME, updated_at DATETIME)
 */
class User {

    private static function db(): PDO {
        return Database::getInstance();
    }

    public static function all(): array {
        $stmt = self::db()->query("SELECT id, name, email, role, avatar, created_at FROM users ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function count(): int {
        $stmt = self::db()->query("SELECT COUNT(*) AS total FROM users");
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public static function updateRole(int $id, string $role): bool {
        $stmt = self::db()->prepare("UPDATE users SET role = ? WHERE id = ?");
        return $stmt->execute([$role, $id]);
    }

    public static function deactivate(int $id): bool {
        if (Database::columnExists('users', 'is_active')) {
            $stmt = self::db()->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
            return $stmt->execute([$id]);
        }

        $stmt = self::db()->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function deleteWithRelations(int $id): bool {
        $db = self::db();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare("DELETE FROM wishlists WHERE user_id = ?");
            $stmt->execute([$id]);

            $stmt = $db->prepare("DELETE FROM reviews WHERE user_id = ?");
            $stmt->execute([$id]);

            $stmt = $db->prepare("DELETE FROM borrows WHERE user_id = ?");
            $stmt->execute([$id]);

            $stmt = $db->prepare("DELETE FROM notifications WHERE user_id = ?");
            $stmt->execute([$id]);

            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            $db->commit();
            return true;
        } catch (Throwable $e) {
            $db->rollBack();
            error_log('User delete failed: ' . $e->getMessage());
            return false;
        }
    }
}
