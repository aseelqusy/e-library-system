<?php

/**
 * User Model
 *
 * Schema:
 *   users (id INT PK AUTO_INCREMENT, name VARCHAR(100), email VARCHAR(150) UNIQUE,
 *          password VARCHAR(255), role ENUM('admin','member'),
 *          is_active TINYINT(1) DEFAULT 1,
 *          avatar VARCHAR(255), phone VARCHAR(20), bio TEXT,
 *          created_at DATETIME, updated_at DATETIME)
 */
class User {

    private static function db(): PDO {
        return Database::getInstance();
    }

    public static function all(): array {
        $hasIsActive = Database::columnExists('users', 'is_active');
        $sql = $hasIsActive
            ? "SELECT id, name, email, role, is_active, avatar, created_at FROM users ORDER BY id DESC"
            : "SELECT id, name, email, role, avatar, created_at FROM users ORDER BY id DESC";
        $stmt = self::db()->query($sql);
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

    public static function create(array $data): int {
        $stmt = self::db()->prepare(
            "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            trim($data['name']     ?? ''),
            trim($data['email']    ?? ''),
            $data['password']      ?? '',
            $data['role']          ?? 'member',
        ]);
        return (int)self::db()->lastInsertId();
    }

    public static function updateProfile(int $id, array $data): bool {
        $fields = [];
        $values = [];

        foreach (['name', 'email', 'avatar', 'phone', 'bio'] as $field) {
            if (array_key_exists($field, $data)) {
                if (Database::columnExists('users', $field) || in_array($field, ['name', 'email', 'avatar'], true)) {
                    $fields[] = "{$field} = ?";
                    $values[] = $data[$field];
                }
            }
        }

        if (empty($fields)) return false;

        $values[] = $id;
        $stmt = self::db()->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function updatePassword(int $id, string $hashedPassword): bool {
        $stmt = self::db()->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $id]);
    }

    public static function updateRole(int $id, string $role): bool {
        $stmt = self::db()->prepare("UPDATE users SET role = ? WHERE id = ?");
        return $stmt->execute([$role, $id]);
    }

    /**
     * Deactivate a user. If the is_active column exists (migration applied),
     * set is_active=0. Otherwise fall back to deleting the user.
     */
    public static function deactivate(int $id): bool {
        if (Database::columnExists('users', 'is_active')) {
            $stmt = self::db()->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
            return $stmt->execute([$id]);
        }

        // Fallback: delete (original schema without is_active column)
        $stmt = self::db()->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Re-activate a previously deactivated user.
     */
    public static function activate(int $id): bool {
        if (Database::columnExists('users', 'is_active')) {
            $stmt = self::db()->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
            return $stmt->execute([$id]);
        }
        // Nothing to do if column doesn't exist
        return true;
    }

    public static function deleteWithRelations(int $id): bool {
        $db = self::db();
        $db->beginTransaction();

        try {
            foreach (['wishlists', 'reviews', 'borrows', 'notifications'] as $table) {
                $stmt = $db->prepare("DELETE FROM {$table} WHERE user_id = ?");
                $stmt->execute([$id]);
            }

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
