-- Admin backend schema updates (optional)
-- Adds optional columns used by profile and deactivation logic.

ALTER TABLE users
    ADD COLUMN phone VARCHAR(20) NULL AFTER avatar,
    ADD COLUMN bio TEXT NULL AFTER phone,
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER role;

