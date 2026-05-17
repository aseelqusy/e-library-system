-- ============================================================
-- Luminara Library — UPGRADE migration (run on existing DB)
-- Safe to run multiple times (uses IF NOT EXISTS / MODIFY).
-- ============================================================

-- 1. Add missing columns to books
ALTER TABLE `books`
  ADD COLUMN IF NOT EXISTS `cover_image` VARCHAR(255) NULL AFTER `description`,
  ADD COLUMN IF NOT EXISTS `pdf_file`    VARCHAR(255) NULL AFTER `cover_image`;

-- 2. Add missing columns to users
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `role`,
  ADD COLUMN IF NOT EXISTS `phone`     VARCHAR(20) NULL AFTER `avatar`,
  ADD COLUMN IF NOT EXISTS `bio`       TEXT NULL AFTER `phone`;

-- 3. Extend borrows status ENUM to include 'rejected'
ALTER TABLE `borrows`
  MODIFY COLUMN `status` ENUM('active','returned','overdue','reserved','rejected') DEFAULT 'active';

-- 4. Ensure the admin seed user exists (safe INSERT IGNORE)
INSERT IGNORE INTO `users` (`name`, `email`, `password`, `role`, `is_active`) VALUES
  ('Admin User', 'admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);
