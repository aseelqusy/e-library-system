# Quick Setup for Book Purchase 🚀

## 1-Minute Setup (Copy & Paste Commands)

### Step 1: Set Up Database

Open Command Prompt/PowerShell and run:

```bash
cd C:\xampp\htdocs\library-app

# Run the FULL schema (if you want a fresh database)
mysql -u root luminara_library < db/luminara_library_FULL.sql

# OR if database exists, run migrations only:
mysql -u root luminara_library < db/migrations/2026_05_21_content_and_orders.sql
mysql -u root luminara_library < db/migrations/2026_05_22_add_price_and_reset_tokens.sql
mysql -u root luminara_library < db/migrations/2026_05_22_add_format_and_purchase_flags.sql
```

### Step 2: Add Test Data

Open MySQL and run:

```sql
-- Insert a test book with price and stock
INSERT INTO books 
  (title, author, category_id, isbn, description, pages, year, publisher, rating, price, copies, available, featured, for_sale, for_borrow) 
VALUES 
  ('The Hobbit', 'J.R.R. Tolkien', 1, '978-0-547-92822-1', 'A fantasy adventure', 310, 1954, 'Allen & Unwin', 4.8, 15.99, 10, 10, 1, 1, 1),
  ('1984', 'George Orwell', 2, '978-0-452-26257-5', 'Dystopian novel', 328, 1949, 'Secker & Warburg', 4.6, 14.99, 5, 5, 1, 1, 1),
  ('To Kill a Mockingbird', 'Harper Lee', 2, '978-0-06-112008-4', 'American classics', 448, 1960, 'J.B. Lippincott & Co.', 4.7, 13.99, 8, 8, 1, 1, 1);

-- Check they were added
SELECT id, title, price, available FROM books WHERE for_sale = 1;
```

### Step 3: Create Test User (Optional)

```sql
-- Insert a test user (password: "password")
INSERT INTO users (name, email, password, role, is_active) 
VALUES ('Test User', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1);

-- Or check existing users
SELECT id, name, email FROM users;
```

---

## Step-by-Step Web Flow

### 1. Start Application
```
http://localhost/library-app/public/
```

### 2. Register or Login
- **New User**: Click Register → Fill form → Click Sign Up
- **Existing User**: Click Login → Enter credentials
- **Admin**: Email: `admin@library.com` Password: `password`

### 3. Browse Books
- Click "Browse Books"
- Look for books with prices
- Click on a book

### 4. Buy Book
- Click "Buy Now"
- Select quantity (1-10)
- Select payment method
- Click "Complete Purchase"

### 5. Verify Purchase
- Go to "My Profile" → "My Orders"
- See your new order with status "Paid"

---

## Check Everything Works

### Verify in Database

Open MySQL and run:

```sql
-- 1. Check books exist with prices
SELECT id, title, price, available FROM books WHERE price > 0 LIMIT 3;

-- 2. Check users exist
SELECT id, name, email FROM users LIMIT 3;

-- 3. Check orders table was created
SHOW TABLES LIKE 'orders';
DESCRIBE orders;

-- 4. After purchase, check orders
SELECT o.id, o.user_id, o.book_id, o.quantity, o.unit_price, o.total_price, o.status 
FROM orders o 
LIMIT 1;

-- 5. Check book stock decreased
SELECT id, title, available FROM books LIMIT 1;
```

### Check Logs

After attempting a purchase, logs appear here:
```
C:\xampp\htdocs\library-app\storage\logs\payment-2026-05-22.log
```

View using PowerShell:
```powershell
Get-Content "C:\xampp\htdocs\library-app\storage\logs\payment-*.log" -Tail 30
```

---

## Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  1. USER LOGS IN                                                │
│     ↓                                                            │
│  2. BROWSE BOOKS (must have: price > 0, available > 0)         │
│     ↓                                                            │
│  3. CLICK "BUY NOW"                                              │
│     ↓                                                            │
│  4. SELECT QUANTITY & PAYMENT METHOD                             │
│     ↓                                                            │
│  5. CLICK "COMPLETE PURCHASE"                                    │
│     ↓                                                            │
│  6. DATABASE TRANSACTION:                                        │
│     ├─ Create ORDER record (orders table)                       │
│     ├─ Decrease AVAILABLE count (books table)                   │
│     └─ COMMIT transaction                                       │
│     ↓                                                            │
│  7. SHOW SUCCESS MESSAGE                                         │
│     ↓                                                            │
│  8. REDIRECT TO MY ORDERS                                        │
│     ↓                                                            │
│  ✅ PURCHASE COMPLETE!                                           │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## If Something Goes Wrong

### Check These in Order

1. **Database Tables Exist**
   ```sql
   SHOW TABLES;
   -- Should include: books, users, orders, categories, etc.
   ```

2. **Books Have Price & Stock**
   ```sql
   SELECT id, title, price, available FROM books WHERE price > 0;
   -- Should show results
   ```

3. **Orders Table Exists**
   ```sql
   DESCRIBE orders;
   -- Should show: id, user_id, book_id, quantity, unit_price, total_price, status, created_at
   ```

4. **Read the Logs**
   ```
   C:\xampp\htdocs\library-app\storage\logs\payment-YYYY-MM-DD.log
   Search for: "ERROR"
   ```

5. **Check PHP Error Log**
   ```
   C:\xampp\php\logs\php_error_log
   ```

---

## Most Common Issues & Fixes

| Error | Fix |
|-------|-----|
| "Table 'orders' doesn't exist" | Run: `mysql -u root luminara_library < db/migrations/2026_05_21_content_and_orders.sql` |
| "Unknown column 'price'" | Run: `mysql -u root luminara_library < db/migrations/2026_05_22_add_price_and_reset_tokens.sql` |
| "Not enough stock" | Insert books with `available > 0`: `INSERT INTO books (...) VALUES (..., 10, 10, ...);` |
| "Book cannot be purchased" | Set `price > 0` and `for_sale = 1` in books table |
| "Table 'luminara_library' doesn't exist" | Create database: `mysql -u root -e "CREATE DATABASE luminara_library"` |
| Not logged in | Visit /login page and sign in |

---

## SQL Queries for Testing

### Quick Verification
```sql
-- Are all tables present?
SELECT COUNT(*) as table_count FROM information_schema.tables 
WHERE table_schema = 'luminara_library';

-- Do books exist with prices?
SELECT COUNT(*) as books_with_price FROM books 
WHERE price > 0 AND available > 0 AND for_sale = 1;

-- Do users exist?
SELECT COUNT(*) as user_count FROM users;

-- How many orders so far?
SELECT COUNT(*) as order_count FROM orders;
```

### After First Purchase
```sql
-- Show the order that was just created
SELECT * FROM orders ORDER BY created_at DESC LIMIT 1;

-- Show book stock after purchase
SELECT id, title, available FROM books WHERE id = 1;

-- Calculate revenue
SELECT SUM(total_price) as total_revenue FROM orders;
```

---

## Everything Ready? ✅

Once you see:
- ✅ Database has books with prices
- ✅ User is logged in
- ✅ Orders appear in "My Orders" after purchase
- ✅ Book stock decreases
- ✅ No errors in logs

**You're all set! 🎉**

The detailed logging system will help you debug any issues. Check `storage/logs/` for details on any failed transactions.


