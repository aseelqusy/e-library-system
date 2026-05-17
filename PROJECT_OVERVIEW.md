# Luminara Library Project Overview

## Summary
Luminara Library is a PHP 8+ MVC web app for managing a library catalog with user accounts, borrowing, wishlists, reviews, and an admin dashboard. It uses a custom router, session-based auth, CSRF protection, and database-backed models via PDO. The UI is a dark glassmorphism theme with animations and a client-side music player widget.

## Architecture
- MVC structure: controllers in `app/Controllers`, models in `app/Models`, views in `app/Views`.
- Single entry point: `public/index.php` defines routes and dispatches via `core/Router.php`.
- Shared infrastructure in `core/`:
  - `Auth.php`: session auth, DB-backed login/register, admin whitelist.
  - `Csrf.php`: token generation/validation.
  - `Database.php`: PDO connection + schema helpers.
  - `Controller.php` and `View.php`: base controller + view rendering.

## Core Features
- Authentication: login/register/logout with session storage and `password_hash` / `password_verify`.
- Catalog: browse, search, categories, book details, featured books.
- Borrowing: request, reserve, return; status updates and availability tracking.
- Wishlist: toggle wishlist items and list user wishlist.
- Reviews: add and display ratings/comments.
- Admin panel: manage books, users, borrows, categories; dashboard analytics.
- Uploads: book cover images and PDF files (validated by MIME/ext and size).

## Database Model (PDO)
The app expects a MySQL database (e.g., `luminara_library`) with tables including:
- `users` (roles, optional `is_active`, profile fields)
- `books` (catalog data; optional `cover_image`, `pdf_file`)
- `categories` (name, slug, icon)
- `borrows` (status, borrow/return dates)
- `reviews` (rating, comment)
- `wishlists` (user-book junction)
- `notifications` (optional)

Migrations are provided in `db/migrations/`:
- `2026_05_15_book_uploads.sql` adds `cover_image` and `pdf_file`.
- `2026_05_15_admin_backend_updates.sql` adds `phone`, `bio`, `is_active`.

## Admin Access Control
Admin access requires:
- Logged-in user with role `admin`.
- Email present in `ADMIN_EMAIL_WHITELIST` (see `config/config.php`).

## Uploads
Uploads are stored under `public/uploads/`:
- Images: `public/uploads/images/`
- PDFs: `public/uploads/pdfs/`

Validation uses:
- Size limits from `config/config.php` (`UPLOAD_MAX_IMAGE_BYTES`, `UPLOAD_MAX_PDF_BYTES`).
- MIME and extension checks in `AdminController::saveUpload`.

## Routing Overview
Defined in `public/index.php`.

Public:
- `GET /` and `/home` (landing)
- `GET /catalog`, `/catalog/search`, `/catalog/categories`
- `GET /books/{id}` and `/book-details/{id}`

User:
- `GET /dashboard`
- `GET/POST /user/profile`
- `GET /user/borrows`, `GET /user/wishlist`, `GET /user/history`
- `POST /user/wishlist/toggle`
- `POST /borrow/request`, `/borrow/return`, `/borrow/reserve`

Admin:
- `GET /admin` (dashboard)
- `GET /admin/books`, `POST /admin/books/store|update|delete`
- `GET /admin/users`, `POST /admin/users/role|deactivate|activate|delete`
- `GET /admin/borrows`, `POST /admin/borrows/return|approve|reject`
- `GET /admin/categories`, `POST /admin/categories/store|update|delete`
- `GET /admin/reports`, `GET/POST /admin/settings`

API (JSON):
- `GET /api/books`, `GET /api/books/{id}`
- `GET /api/categories`, `GET /api/featured`, `GET /api/search`
- `POST /api/review`

## Analytics
Admin dashboard metrics are computed from the database:
- Totals: books, users, borrows, reviews, wishlists, categories.
- Monthly deltas using `created_at` and `borrow_date`.
- Charts: borrows by month, books per category.
- Recent activity: recent borrow records with user and book details.

## UI/Assets
- Styles: `public/assets/css/styles.css`.
- Scripts: `public/assets/js/app.js`, `animations.js`, `music-player.js`, `form-validation.js`.
- Images and sample uploads in `public/uploads/`.

## Configuration
Key settings in `config/config.php`:
- Paths and base URL.
- DB connection params.
- Session settings.
- Upload limits.
- Admin email whitelist.

## Notable Implementation Details
- `Book::normalise` provides consistent `cover_image` and `pdf_file` keys across schemas.
- `Borrow::countByMonth` and `Category::countsWithBooks` power dashboard charts.
- `User::deleteWithRelations` removes dependent data in a transaction.
- `AdminController::updateSettings` is currently UI-only (no DB persistence).

## Gaps / Follow-ups
- `README.md` still states "No Database Required" even though the app now uses PDO.
- Settings persistence is not implemented server-side.
- Ensure the database has the expected `created_at` columns used in analytics.

## Entry Points and Key Files
- `public/index.php` (router + app bootstrap)
- `config/config.php` (app + DB config)
- `core/Auth.php`, `core/Router.php`, `core/Database.php`
- `app/Controllers/*` (request handlers)
- `app/Models/*` (DB access)
- `app/Views/*` (UI)
