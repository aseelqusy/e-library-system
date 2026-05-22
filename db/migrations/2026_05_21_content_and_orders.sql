-- 2026-05-21: Orders, activities, quotes, contact messages

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending','paid','cancelled','refunded') NOT NULL DEFAULT 'paid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_book (book_id),
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_orders_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) NULL,
    activity_date DATE NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activity_date (activity_date)
);

CREATE TABLE IF NOT EXISTS quotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quote_text TEXT NOT NULL,
    quote_author VARCHAR(160) NULL,
    source VARCHAR(160) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    subject VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO quotes (quote_text, quote_author, source, is_active)
SELECT 'A reader lives a thousand lives before he dies.', 'George R.R. Martin', 'A Dance with Dragons', 1
WHERE NOT EXISTS (SELECT 1 FROM quotes LIMIT 1);

INSERT INTO activities (title, description, image_path, activity_date, is_active)
SELECT title, description, image_path, activity_date, is_active
FROM (
    SELECT 'Monthly Reading Circle' AS title,
           'Join fellow readers for a guided discussion on this month''s most borrowed book.' AS description,
           'uploads/activities/reading-circle.svg' AS image_path,
           CURDATE() AS activity_date,
           1 AS is_active
    UNION ALL
    SELECT 'Storytelling Workshop',
           'Discover new ways to lead family-friendly story sessions in the library.',
           'uploads/activities/book-club.svg',
           DATE_ADD(CURDATE(), INTERVAL 7 DAY),
           1
    UNION ALL
    SELECT 'Library Tour Day',
           'Welcome new members with a guided tour of study spaces, archives, and reading corners.',
           'uploads/activities/library-tour.svg',
           DATE_ADD(CURDATE(), INTERVAL 14 DAY),
           1
) AS sample_activities
WHERE NOT EXISTS (SELECT 1 FROM activities LIMIT 1);

UPDATE activities
SET image_path = CASE MOD(id - 1, 3)
    WHEN 0 THEN 'uploads/activities/reading-circle.svg'
    WHEN 1 THEN 'uploads/activities/book-club.svg'
    ELSE 'uploads/activities/library-tour.svg'
END
WHERE image_path IS NULL OR image_path = '';

