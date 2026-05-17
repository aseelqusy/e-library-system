-- ============================================================
-- Luminara Library — FULL schema (replaces original + migrations)
-- Run this on a fresh database to get everything in one shot.
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- --------------------------------------------------------
-- books
-- --------------------------------------------------------
CREATE TABLE `books` (
  `id`          int(11)        NOT NULL AUTO_INCREMENT,
  `title`       varchar(255)   NOT NULL,
  `author`      varchar(150)   NOT NULL,
  `category_id` int(11)        DEFAULT NULL,
  `isbn`        varchar(20)    DEFAULT NULL,
  `description` text           DEFAULT NULL,
  `cover`       varchar(255)   DEFAULT NULL,
  `cover_image` varchar(255)   DEFAULT NULL,   -- added by migration
  `pdf_file`    varchar(255)   DEFAULT NULL,   -- added by migration
  `pages`       int(11)        DEFAULT 0,
  `year`        int(11)        DEFAULT NULL,
  `publisher`   varchar(150)   DEFAULT NULL,
  `rating`      decimal(2,1)   DEFAULT 0.0,
  `copies`      int(11)        DEFAULT 1,
  `available`   int(11)        DEFAULT 1,
  `featured`    tinyint(1)     DEFAULT 0,
  `created_at`  timestamp      NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `isbn` (`isbn`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- categories
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id`          int(11)        NOT NULL AUTO_INCREMENT,
  `name`        varchar(100)   NOT NULL,
  `slug`        varchar(100)   NOT NULL,
  `description` text           DEFAULT NULL,
  `icon`        varchar(10)    DEFAULT '📁',
  `created_at`  timestamp      NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id`          int(11)        NOT NULL AUTO_INCREMENT,
  `name`        varchar(100)   NOT NULL,
  `email`       varchar(150)   NOT NULL,
  `password`    varchar(255)   NOT NULL,
  `role`        enum('admin','member') DEFAULT 'member',
  `is_active`   tinyint(1)     NOT NULL DEFAULT 1,   -- added by migration
  `avatar`      varchar(255)   DEFAULT NULL,
  `phone`       varchar(20)    DEFAULT NULL,          -- added by migration
  `bio`         text           DEFAULT NULL,           -- added by migration
  `created_at`  timestamp      NOT NULL DEFAULT current_timestamp(),
  `updated_at`  timestamp      NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- borrows  (status includes 'rejected')
-- --------------------------------------------------------
CREATE TABLE `borrows` (
  `id`          int(11)        NOT NULL AUTO_INCREMENT,
  `user_id`     int(11)        NOT NULL,
  `book_id`     int(11)        NOT NULL,
  `borrow_date` date           NOT NULL,
  `due_date`    date           NOT NULL,
  `return_date` date           DEFAULT NULL,
  `status`      enum('active','returned','overdue','reserved','rejected') DEFAULT 'active',
  `created_at`  timestamp      NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `book_id` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- notifications
-- --------------------------------------------------------
CREATE TABLE `notifications` (
  `id`          int(11)        NOT NULL AUTO_INCREMENT,
  `user_id`     int(11)        NOT NULL,
  `message`     text           NOT NULL,
  `type`        varchar(20)    DEFAULT 'info',
  `is_read`     tinyint(1)     DEFAULT 0,
  `created_at`  timestamp      NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- reviews
-- --------------------------------------------------------
CREATE TABLE `reviews` (
  `id`          int(11)        NOT NULL AUTO_INCREMENT,
  `user_id`     int(11)        NOT NULL,
  `book_id`     int(11)        NOT NULL,
  `rating`      int(11)        NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `comment`     text           DEFAULT NULL,
  `created_at`  timestamp      NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `book_id` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- wishlists
-- --------------------------------------------------------
CREATE TABLE `wishlists` (
  `id`          int(11)        NOT NULL AUTO_INCREMENT,
  `user_id`     int(11)        NOT NULL,
  `book_id`     int(11)        NOT NULL,
  `created_at`  timestamp      NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_wishlist` (`user_id`,`book_id`),
  KEY `book_id` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Foreign keys
-- --------------------------------------------------------
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

ALTER TABLE `borrows`
  ADD CONSTRAINT `borrows_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrows_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------
-- Seed: default admin user  (password = "password")
-- --------------------------------------------------------
INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`) VALUES
  ('Admin User', 'admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

COMMIT;
