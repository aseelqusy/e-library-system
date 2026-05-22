<?php

class PasswordReset {

    private static function db(): PDO {
        return Database::getInstance();
    }

    /**
     * Ensure the password reset tokens table exists
     */
    public static function ensureTable(): void {
        self::db()->exec(
            "CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `token` VARCHAR(255) NOT NULL UNIQUE,
                `expires_at` TIMESTAMP NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_token` (`token`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_expires_at` (`expires_at`),
                CONSTRAINT `fk_password_reset_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    /**
     * Generate a secure reset token
     */
    public static function generateToken(): string {
        return bin2hex(random_bytes(32));
    }

    /**
     * Create a password reset token for a user
     *
     * @param int $userId User ID
     * @param int $expiresInHours Hours until token expires (default: 24)
     * @return string The generated token
     */
    public static function createToken(int $userId, int $expiresInHours = 24): string {
        self::ensureTable();

        // Remove any existing tokens for this user
        self::deleteUserTokens($userId);

        $token = self::generateToken();
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresInHours} hours"));

        $stmt = self::db()->prepare(
            "INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)"
        );
        $stmt->execute([$userId, $token, $expiresAt]);

        return $token;
    }

    /**
     * Validate a reset token
     *
     * @param string $token The token to validate
     * @return array|null User data if valid, null if invalid or expired
     */
    public static function validateToken(string $token): ?array {
        self::ensureTable();

        $stmt = self::db()->prepare(
            "SELECT prt.user_id, prt.expires_at, u.* 
             FROM password_reset_tokens prt
             JOIN users u ON u.id = prt.user_id
             WHERE prt.token = ? AND prt.expires_at > NOW()"
        );
        $stmt->execute([$token]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Get a token record by token string
     */
    public static function getByToken(string $token): ?array {
        self::ensureTable();

        $stmt = self::db()->prepare(
            "SELECT * FROM password_reset_tokens WHERE token = ?"
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Delete a token (after successful reset)
     */
    public static function deleteToken(string $token): bool {
        self::ensureTable();

        $stmt = self::db()->prepare(
            "DELETE FROM password_reset_tokens WHERE token = ?"
        );
        return $stmt->execute([$token]);
    }

    /**
     * Delete all tokens for a user
     */
    public static function deleteUserTokens(int $userId): bool {
        self::ensureTable();

        $stmt = self::db()->prepare(
            "DELETE FROM password_reset_tokens WHERE user_id = ?"
        );
        return $stmt->execute([$userId]);
    }

    /**
     * Clean up expired tokens (call periodically)
     */
    public static function cleanupExpiredTokens(): int {
        self::ensureTable();

        $stmt = self::db()->prepare(
            "DELETE FROM password_reset_tokens WHERE expires_at < NOW()"
        );
        $stmt->execute();
        return self::db()->lastInsertId();
    }

    /**
     * Log password reset action (optional for audit trail)
     */
    public static function logAction(int $userId, string $action, ?string $ipAddress = null, ?string $userAgent = null): void {
        self::db()->exec(
            "CREATE TABLE IF NOT EXISTS `password_reset_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `ip_address` VARCHAR(50) DEFAULT NULL,
                `user_agent` TEXT DEFAULT NULL,
                `action` ENUM('request','reset','failed') DEFAULT 'request',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_user_id` (`user_id`),
                KEY `idx_created_at` (`created_at`),
                CONSTRAINT `fk_password_reset_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $stmt = self::db()->prepare(
            "INSERT INTO password_reset_logs (user_id, ip_address, user_agent, action) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $ipAddress, $userAgent, $action]);
    }
}

