<?php

class Auth {

    private static array $defaultUsers = [];

    private static function hasSessionVersionColumn(): bool {
        return Database::columnExists('users', 'session_version');
    }

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

            if (self::hasSessionVersionColumn()) {
                $_SESSION['user']['session_version'] = (int)($user['session_version'] ?? 0);
            }

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
        if (!isset($_SESSION['user'])) {
            return false;
        }

        if (self::hasSessionVersionColumn()) {
            $sessionVersion = (int)($_SESSION['user']['session_version'] ?? -1);
            $stmt = Database::getInstance()->prepare("SELECT session_version FROM users WHERE id = ?");
            $stmt->execute([(int)($_SESSION['user']['id'] ?? 0)]);
            $current = $stmt->fetch();

            if (!$current || (int)($current['session_version'] ?? -1) !== $sessionVersion) {
                self::logout();
                return false;
            }
        }

        return true;
    }

    public static function user(): ?array {
        return self::check() ? ($_SESSION['user'] ?? null) : null;
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
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
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
        $fields = ['name', 'email', 'avatar', 'password', 'is_active'];
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

            if (array_key_exists('is_active', $data) && (int)$data['is_active'] === 0) {
                self::logout();
            }

            if (array_key_exists('password', $data)) {
                unset($_SESSION['user']['session_version']);
            }
        }

        return $ok;
    }

    public static function logoutAllSessions(): bool {
        if (!self::check()) {
            return false;
        }

        if (self::hasSessionVersionColumn()) {
            $stmt = Database::getInstance()->prepare("UPDATE users SET session_version = COALESCE(session_version, 0) + 1 WHERE id = ?");
            $stmt->execute([self::id()]);
        }

        self::logout();
        return true;
    }
}
