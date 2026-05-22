# Audit Summary

## Major Fixes Made
- Removed the GitHub social login button from the login page.
- Standardized validation messages so required-field errors read clearly, including:
  - `Email is required`
  - `Password is required`
- Unified CSRF handling across forms and AJAX requests by accepting the shared `_token` field and `csrf_token` fallback.
- Fixed the session bootstrap order and hardened session cookie settings.
- Reworked profile security actions so users can:
  - update profile
  - change password
  - delete account
  - log out from all sessions when supported by the database
- Replaced the old “Welcome Back” login copy with neutral sign-in messaging.
- Added a reusable shared footer component and applied it consistently through the layout.
- Standardized auth spacing, form alignment, and layout gaps.
- Fixed book cover rendering so covers fit correctly with `object-fit: cover`.
- Implemented a full review CRUD flow with live create/edit/delete behavior.
- Added real-time review list updates and instant average rating recalculation.
- Added polling-based notification refresh and unread badge synchronization.
- Redesign search to support debounced live querying, richer ranking, and suggestion rendering.
- Reduced admin/public rendering drift by passing role/auth state into the layout.

## Architectural Improvements
- The app now treats MySQL/PDO as the primary data source instead of placeholder/static data.
- Review and notification data now flow through database-driven controller/model paths.
- Book search ranking is computed server-side from database-backed records.
- The notification system now has a reusable polling endpoint, making the navbar UI easier to keep in sync.
- The review page now has stable DOM hooks for incremental UI updates instead of full page reloads.

## Remaining Technical Concerns
- Global session invalidation works best when the `users.session_version` column exists; without it, the app falls back to current-session logout only.
- Search ranking is server-side and database-backed, but still uses application-level scoring rather than a dedicated database search index.

## Suggested Future Improvements
- Add persisted application settings storage.
- Add a dedicated notification read endpoint for individual notifications if fine-grained inbox actions are needed.
- Introduce full-text search indexes for large catalogs.
- Add server-side pagination to catalog and search endpoints for larger datasets.
- Add automated browser tests for auth, reviews, and responsive navigation.
- Extract more shared UI fragments into reusable partials for easier long-term maintenance.

## Phase 2 (May 21, 2026) Additions

### Major Fixes Made
- Added a dedicated Admin Reviews page (`admin/reviews`) with:
  - database-backed listing of all reviews
  - search by user/book/comment
  - rating filter
  - pagination
  - secure admin delete flow
- Added About and Contact pages with consistent design language and responsive layouts.
- Implemented contact form validation and persistence through a new `contact_messages` table.
- Implemented Buy Book feature:
  - new backend order processing endpoint (`order/buy`)
  - secure validation for quantity, stock, and payload
  - order persistence in database (`orders` table)
- Added My Orders user experience:
  - new route/page (`user/orders`)
  - dashboard quick action
  - responsive order cards with quantity, price, status, and date
- Redesigned footer into a reusable multi-column responsive component with consistent spacing and navigation.
- Added dynamic Home content:
  - quote section now database-driven with automatic rotation
  - new database-driven Library Activities section
- Expanded role-safe command palette suggestions and removed cross-role leakage by role checks.

### Architectural Improvements
- Introduced new modular models: `Order`, `Activity`, `Quote`, and `ContactMessage`.
- Added migration `db/migrations/2026_05_21_content_and_orders.sql` for new domain tables.
- Kept implementation backward-compatible by using `CREATE TABLE IF NOT EXISTS` safeguards in model-level initializers.

### Remaining Technical Concerns
- `OrderController::resolvePrice()` currently computes a derived catalog price from existing book attributes; this should migrate to a dedicated persistent `books.price` column for accounting-grade pricing.
- Model-level `ensureTable()` bootstrapping is practical for compatibility, but long-term production should rely on explicit migration pipelines only.

### Stabilization Pass (May 21, 2026)
- Removed unreachable branches in `app/Controllers/UserController.php` (post-redirect `return` statements).
- Added defensive view defaults in:
  - `app/Views/home/landing.php`
  - `app/Views/pages/about.php`
  - `app/Views/pages/contact.php`
  - `app/Views/user/my-orders.php`
- Reduced JS inspection noise in `public/assets/js/app.js` by removing an unused callback parameter and centralizing modal close handling.
- Re-ran IDE diagnostics (`get_errors`) for updated files:
  - no PHP view/controller errors in the modified templates/controllers.
  - one remaining IDE warning in `public/assets/js/app.js` (`Unused function close`) appears to be inspection-level and not a runtime blocker.
- CLI lint/build verification could not be completed in-session due terminal execution returning only prompt echo (`>>`) with no command output.

