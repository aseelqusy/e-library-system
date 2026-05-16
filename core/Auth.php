<?php

class Auth {

    private static array $defaultUsers = [];

    public static function init(): void {
        // No-op: authentication uses database records only.
    }

    public static function getUsers(): array {
        $stmt = Database::getInstance()->query("SELECT id, name, email, role, avatar, created_at FROM users ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public static function attempt(string $email, string $password): bool {
        $stmt = Database::getInstance()->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if (Database::columnExists('users', 'is_active') && (int)$user['is_active'] === 0) {
                return false;
            }
            $_SESSION['user'] = [
                'id'         => $user['id'],
                'name'       => $user['name'],
                'email'      => $user['email'],
                'role'       => $user['role'],
                'avatar'     => $user['avatar'],
                'session_id' => session_id(),
                'logged_in_at' => date('c'),
            ];
            return true;
        }
        return false;
    }

    public static function register(string $name, string $email, string $password): bool {
        $db = Database::getInstance();
        $check = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) return false; // email taken

        $stmt = $db->prepare(
            "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'member')"
        );
        return $stmt->execute([$name, $email, password_hash($password, PASSWORD_BCRYPT)]);
    }

    public static function check(): bool {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public static function isAdmin(): bool {
        return self::check() && ($_SESSION['user']['role'] ?? '') === 'admin';
    }

    public static function isAdminAuthorized(): bool {
        if (!self::check()) return false;
        if (!self::isAdmin()) return false;

        $email = strtolower(trim($_SESSION['user']['email'] ?? ''));
        $whitelist = array_map(fn($item) => strtolower(trim($item)), ADMIN_EMAIL_WHITELIST ?? []);

        return in_array($email, $whitelist, true);
    }

    public static function logout(): void {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    public static function id(): ?int {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function getUserById(int $id): ?array {
        $stmt = Database::getInstance()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function updateUser(int $id, array $data): bool {
        $fields = ['name', 'email', 'avatar'];
        if (Database::columnExists('users', 'phone')) $fields[] = 'phone';
        if (Database::columnExists('users', 'bio')) $fields[] = 'bio';

        $set = [];
        $values = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $set[] = "{$field} = ?";
                $values[] = $data[$field] === '' ? null : $data[$field];
            }
        }

        if (empty($set)) return false;

        $values[] = $id;
        $stmt = Database::getInstance()->prepare("UPDATE users SET " . implode(', ', $set) . " WHERE id = ?");
        $ok = $stmt->execute($values);

        if ($ok && Auth::id() === $id) {
            $_SESSION['user']['name']  = $data['name'] ?? $_SESSION['user']['name'];
            $_SESSION['user']['email'] = $data['email'] ?? $_SESSION['user']['email'];
            if (isset($data['avatar'])) $_SESSION['user']['avatar'] = $data['avatar'];
        }

        return $ok;
    }
}
