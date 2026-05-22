# How to Successfully Buy a Book 📚

## Quick Summary

To successfully buy a book, you need to:
1. ✅ Have a database with all migrations applied
2. ✅ Have a book in the catalog with price and available stock
3. ✅ Be logged in as a user
4. ✅ Navigate to the book detail page
5. ✅ Click "Buy Now" and complete the purchase

---

## Step 1: Set Up Database with All Migrations ⚙️

### Run Migrations in Order

Your database needs these tables and columns:

**Option A: Run Full Schema (Fresh Database)**
```bash
# Use the complete schema file
mysql -u root -p luminara_library < db/luminara_library_FULL.sql
```

**Option B: Run Individual Migrations (Existing Database)**
```bash
# Run in this order:
mysql -u root -p luminara_library < db/migrations/2026_05_21_content_and_orders.sql
mysql -u root -p luminara_library < db/migrations/2026_05_22_add_price_and_reset_tokens.sql
mysql -u root -p luminara_library < db/migrations/2026_05_22_add_format_and_purchase_flags.sql
```

### What Gets Created/Added

| Table/Column | Purpose |
|---|---|
| `orders` table | Stores all purchase transactions |
| `books.price` | Price for each book (DECIMAL 10,2) |
| `books.format` | Format: written, audio, or both |
| `books.for_sale` | Whether book can be purchased (0/1) |
| `books.for_borrow` | Whether book can be borrowed (0/1) |

---

## Step 2: Add Test Books to Catalog 📖

### Option A: Use Admin Panel
1. Log in as admin (email: `admin@library.com`, password: `password`)
2. Go to **Admin Panel** → **Books**
3. Click **Add Book**
4. Fill in details:
   - **Title**: Required
   - **Author**: Required
   - **Category**: Select one
   - **Price**: Must be > 0 (e.g., 15.99)
   - **Available**: Must be > 0 (e.g., 10)
   - **Copies**: Total copies (e.g., 10)
   - **For Sale**: Check this box ✓

### Option B: Insert via SQL
```sql
INSERT INTO books 
  (title, author, category_id, isbn, description, pages, year, publisher, rating, price, copies, available, featured, for_sale, for_borrow) 
VALUES 
  ('The Hobbit', 'J.R.R. Tolkien', 1, '978-0-547-92822-1', 'A fantasy adventure', 310, 1954, 'Allen & Unwin', 4.8, 15.99, 10, 10, 1, 1, 1);
```

### Verify Books Exist
```sql
-- Check books with price and stock
SELECT id, title, author, price, available FROM books 
WHERE available > 0 AND price > 0 AND for_sale = 1;
```

---

## Step 3: Create a User Account 👤

### Option A: Register via Web
1. Go to home page
2. Click **Register**
3. Fill in:
   - Name
   - Email
   - Password
   - Confirm Password
4. Click **Sign Up**
5. Log in with your credentials

### Option B: Insert via SQL
```sql
-- Password "password" = $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi (bcrypt)
INSERT INTO users (name, email, password, role, is_active) 
VALUES ('Test User', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1);
```

### Verify User Exists
```sql
SELECT id, name, email, role FROM users WHERE email = 'user@example.com';
```

---

## Step 4: Purchase a Book 🛒

### Step-by-Step Instructions

1. **Log In**
   - Go to home page
   - Click **Login**
   - Enter your email and password
   - Click **Sign In**

2. **Find a Book**
   - Click **Browse Books** or search
   - Look for books with prices and in stock
   - Click on a book title

3. **View Book Details**
   - See price, description, availability
   - Confirm **Available** > 0 and **Price** > 0

4. **Purchase**
   - Click **Buy Now** button
   - You'll see the checkout page
   - Default quantity is 1
   - Optional: Change quantity (1-10)
   - Select payment method:
     - **Card** - Mock credit card payment
     - **PayPal** - Mock PayPal payment
     - **Bank Transfer** - Mock bank transfer
   - Click **Complete Purchase**

5. **Confirmation**
   - You should see: "Payment processed successfully"
   - You'll be redirected to **My Orders**
   - Your order appears in the list
   - Book's available stock decreases

---

## Step 5: Verify Purchase Success ✅

### In Web UI
1. Go to **My Profile** → **My Orders**
2. You should see your new order with:
   - Book title
   - Quantity purchased
   - Total price
   - Status: "Paid"
   - Date/time of purchase

### In Database
```sql
-- Check orders
SELECT o.*, b.title 
FROM orders o 
JOIN books b ON o.book_id = b.id 
WHERE o.user_id = 1 
ORDER BY o.id DESC;

-- Check book stock decreased
SELECT id, title, available, price FROM books WHERE id = 1;
```

### In Log Files
Check: `C:\xampp\htdocs\library-app\storage\logs\payment-YYYY-MM-DD.log`

Should contain:
```
[timestamp] Payment processing started | {"user_id":1,"book_id":1,...}
[timestamp] Book retrieved | {"title":"Book Title","available":10,"price":15.99}
[timestamp] Transaction committed successfully | {"order_id":123}
```

---

## Troubleshooting: If Purchase Fails ❌

### Check the Logs
1. Open: `C:\xampp\htdocs\library-app\storage\logs\`
2. Look for: `payment-2026-05-22.log` (today's date)
3. Search for: "ERROR"
4. Read the exception message

### Common Issues & Fixes

| Issue | Solution |
|-------|----------|
| "Not logged in" | Log in first at `/login` |
| "Book not found" | Make sure book exists in DB with valid ID |
| "Not enough stock" | Increase book's `available` count in DB |
| "Book cannot be purchased" | Set book's `price > 0` and `for_sale = 1` |
| "Table doesn't exist" | Run migrations: `db/migrations/2026_05_21_content_and_orders.sql` |
| "Unknown column 'price'" | Run migration: `db/migrations/2026_05_22_add_price_and_reset_tokens.sql` |
| "CSRF validation failed" | Refresh page and try again |

---

## Complete Setup Checklist

- [ ] Database created: `luminara_library`
- [ ] All migrations applied (orders table created, price column added)
- [ ] Admin user exists: `admin@example.com` with password `password`
- [ ] Test user account created and logged in
- [ ] Books added to catalog with:
  - [ ] Title and author
  - [ ] Price > 0
  - [ ] Available > 0
  - [ ] For Sale = checked (1)
- [ ] User is logged in before purchasing
- [ ] Clicked "Buy Now" on a book
- [ ] Selected payment method
- [ ] Completed purchase
- [ ] Order appears in "My Orders"
- [ ] Book stock decreased in database
- [ ] No errors in `storage/logs/` files

---

## Database Structure for Purchase

```
users
├── user_id (PK)
├── name
├── email
└── password

books
├── id (PK)
├── title
├── price (DECIMAL 10,2) ← Must be > 0
├── available (INT) ← Must be > 0
├── copies (INT)
├── for_sale (TINYINT) ← Must be 1
└── ... other fields ...

orders (NEW TABLE)
├── id (PK) ← Created on purchase
├── user_id (FK → users)
├── book_id (FK → books)
├── quantity
├── unit_price
├── total_price
├── status (ENUM: 'pending','paid','cancelled','refunded')
└── created_at

Purchase Flow:
  User (logged in) ──→ Book (price > 0, available > 0) ──→ Order created ──→ Stock decreased
```

---

## Key Files for Purchase Functionality

| File | Purpose |
|------|---------|
| `app/Controllers/PaymentController.php` | Handles `/payment/process` route |
| `app/Controllers/OrderController.php` | Handles `/order/buy` route |
| `app/Models/Order.php` | Creates orders in database |
| `app/Models/Book.php` | Updates book availability |
| `core/Helpers.php` | Contains `logDebug()` and `logError()` for debugging |

---

## Testing Purchase Flow

### Quick Test Script

```php
// Test in browser console or PHP script
$bookId = 1;
$bookUrl = 'http://localhost/library-app/public/index.php?url=books/' . $bookId;

// 1. Visit book page
echo "Visit: " . $bookUrl;

// 2. Check database before purchase
$before = "SELECT available FROM books WHERE id = 1";

// 3. Click buy and complete purchase

// 4. Check database after purchase
$after = "SELECT available FROM books WHERE id = 1";
// Should be decreased by 1 (or quantity purchased)

// 5. Check orders table
$order = "SELECT * FROM orders WHERE user_id = 1 ORDER BY id DESC LIMIT 1";
```

---

## Success Indicators ✅

A successful purchase will:
- ✅ Create an order record in the `orders` table
- ✅ Decrease the book's `available` count
- ✅ Show "Payment processed successfully" message
- ✅ Redirect to user's orders page
- ✅ Display the order in "My Orders" with status "Paid"
- ✅ Write success logs to `storage/logs/payment-*.log`

---

## Need Help?

If your purchase fails:

1. **Check logs**: `storage/logs/payment-2026-05-22.log`
2. **Read error message**: Look for "ERROR:" in logs
3. **Match to troubleshooting**: Use table above
4. **Apply fix**: Run migrations, add data, etc.
5. **Try again**: Attempt purchase again

The detailed logging system will tell you EXACTLY what went wrong!


