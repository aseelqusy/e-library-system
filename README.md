# 📚 Luminara Library – Library Management Web App

A modern, dark-themed library management web application built with PHP 8+ MVC architecture, vanilla JavaScript, and a glassmorphism UI featuring animated butterflies, floating books, and an ambient music player.

---

## ✨ Features

- **Dark Glassmorphism UI** — Frosted-glass cards, purple/cyan glow accents, animated decorations
- **Butterfly & Book Animations** — SVG butterflies with wing-flap + floating book emojis
- **Ambient Music Player** — Collapsible widget with playlist, volume, ambient mode (localStorage prefs)
- **Command Palette** — `Ctrl + K` quick-search overlay
- **Responsive Design** — Mobile-first breakpoints at 480/768/1024px
- **Session-Based Auth** — Login, register, role-based access (admin/member)
- **Full Catalog** — Browse, search, filter by category, sort, book details with reviews
- **User Dashboard** — Profile, active borrows, wishlist, borrowing history
- **Admin Panel** — Dashboard with charts, manage books/users/borrows/categories, reports, settings
- **CSRF Protection** — Token-based form protection
- **No Database Required** — Runs entirely on placeholder arrays, ready to swap to MySQL/PDO

---

## 🗂 Project Structure

```
library-app/
├── app/
│   ├── Controllers/        # 8 controllers (Home, Auth, Catalog, Book, User, Borrow, Admin, Api)
│   ├── Models/             # 6 models with placeholder data (Book, Category, Borrow, Review, Notification, User)
│   └── Views/
│       ├── admin/          # dashboard, books, users, borrows, categories, reports, settings, sidebar
│       ├── auth/           # login, register, forgot
│       ├── catalog/        # browse, book-details, categories, search
│       ├── errors/         # 404, 403
│       ├── home/           # landing
│       ├── layouts/        # header, navbar, footer
│       └── user/           # profile, my-borrows, wishlist, history
├── config/
│   └── config.php          # Constants, paths, DB placeholders
├── core/
│   ├── Auth.php            # Session-based authentication
│   ├── Controller.php      # Base controller
│   ├── Csrf.php            # CSRF token generation/validation
│   ├── Helpers.php         # Utility functions
│   ├── Router.php          # URL routing with param support
│   ├── Validator.php       # Chainable form validation
│   └── View.php            # View renderer with layouts
├── public/
│   ├── assets/
│   │   ├── css/styles.css  # ~900 lines, full theme
│   │   ├── js/             # app.js, animations.js, music-player.js, form-validation.js
│   │   ├── audio/          # (placeholder – add .mp3 files here)
│   │   └── img/            # (placeholder – add images here)
│   ├── index.php           # Entry point with route definitions
│   └── .htaccess           # URL rewriting
├── storage/logs/           # Application logs
├── .htaccess               # Root – blocks direct access to app/core/config
└── README.md               # This file
```

---

## 🚀 Setup (XAMPP)

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) with PHP 8.0+
- Apache `mod_rewrite` enabled

### Installation

1. **Clone / copy** the `library-app` folder into your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\library-app\
   ```

2. **Verify `mod_rewrite`** is enabled in `httpd.conf`:
   ```
   LoadModule rewrite_module modules/mod_rewrite.so
   ```

3. **Start Apache** from the XAMPP Control Panel.

4. **Open in browser**:
   ```
   http://localhost/library-app/public/
   ```

### Demo Accounts

| Role   | Email              | Password   |
|--------|--------------------|------------|
| Admin  | admin@library.com  | admin123   |
| Member | john@library.com   | user123    |
| Member | jane@library.com   | user123    |

---

## 🎵 Music Player

The floating music player appears in the bottom-right corner. It uses placeholder track metadata — to enable actual audio:

1. Place `.mp3` files in `public/assets/audio/`
2. Update the `tracks` array in `public/assets/js/music-player.js`
3. Each track needs: `title`, `artist`, `file` (filename)

---

## 🗄 Database Migration Guide

The app currently uses in-memory arrays. To switch to MySQL:

### 1. Create Database

```sql
CREATE DATABASE luminara_library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE luminara_library;
```

### 2. Create Tables

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','member') DEFAULT 'member',
    avatar VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(10) DEFAULT '📁',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(150) NOT NULL,
    category_id INT,
    isbn VARCHAR(20) UNIQUE,
    description TEXT,
    cover VARCHAR(255),
    pages INT DEFAULT 0,
    year INT,
    publisher VARCHAR(150),
    rating DECIMAL(2,1) DEFAULT 0.0,
    copies INT DEFAULT 1,
    available INT DEFAULT 1,
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE borrows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('active','returned','overdue','reserved') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(20) DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE wishlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, book_id)
);
```

### 3. Update Models

Replace static array methods with PDO queries. Example for `Book::all()`:

```php
// Before (placeholder):
public static function all(): array {
    return self::$books;
}

// After (MySQL):
public static function all(): array {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT * FROM books ORDER BY title");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

### 4. Update Auth

Replace `Auth::$users` session storage with database queries using `password_hash()` / `password_verify()` (already used in the current code).

### 5. Update Config

Set your database credentials in `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'luminara_library');
define('DB_USER', 'root');
define('DB_PASS', '');
```

---

## 🛠 Coding Standards

- **PHP 8+** with strict typing where applicable
- **No frameworks / No npm** — pure PHP + Vanilla JS + CSS
- **MVC Pattern** — Controllers handle logic, Models hold data, Views render HTML
- **Security** — CSRF tokens, `htmlspecialchars()` output escaping, `password_hash()` for auth
- **Responsive** — Mobile-first with breakpoints at 480px, 768px, 1024px

---

## 📝 License

This project is for educational / demonstration purposes.
