-- ============================================================
-- Luminara Library — Add Format & Purchase Flags
-- May 22, 2026
-- ============================================================

-- Add columns to support format and purchase options
ALTER TABLE `books`
  ADD COLUMN IF NOT EXISTS `format` ENUM('written', 'audio', 'both') DEFAULT 'written' AFTER `price`,
  ADD COLUMN IF NOT EXISTS `for_sale` TINYINT(1) DEFAULT 1 AFTER `format`,
  ADD COLUMN IF NOT EXISTS `for_borrow` TINYINT(1) DEFAULT 1 AFTER `for_sale`;

-- Add indexes for better filtering performance
ALTER TABLE `books`
  ADD INDEX IF NOT EXISTS `idx_format` (`format`),
  ADD INDEX IF NOT EXISTS `idx_for_sale` (`for_sale`),
  ADD INDEX IF NOT EXISTS `idx_for_borrow` (`for_borrow`);

COMMIT;

