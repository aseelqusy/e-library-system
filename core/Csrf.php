<?php

class Csrf {
    public static function init(): void {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function token(): string {
        return $_SESSION['_csrf_token'] ?? '';
    }

    public static function field(): string {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate(string $token): bool {
        return hash_equals(self::token(), $token);
    }

    public static function regenerate(): void {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
}
