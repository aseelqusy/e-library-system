ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `reset_token_hash` VARCHAR(64) DEFAULT NULL AFTER `role`,
    ADD COLUMN IF NOT EXISTS `reset_token_expires` DATETIME DEFAULT NULL AFTER `reset_token_hash`;

