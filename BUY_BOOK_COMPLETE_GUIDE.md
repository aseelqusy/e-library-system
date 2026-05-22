# ✅ Complete Guide: How to Buy a Book Successfully

## Executive Summary

A user can buy a book successfully by following this flow:

```
User Logs In → Browse Books → Click "Buy Now" → Select Payment → Complete Purchase
     ↓            ↓              ↓                     ↓                  ↓
  Required    Price > 0      Quantity 1-10      Card/PayPal/       Order Created
             available > 0    Confirm             Transfer              Stock ↓
```

---

## 3 Essential Prerequisites

### 1️⃣ Database Must Be Set Up
```bash
# Run this command once
mysql -u root luminara_library < db/luminara_library_FULL.sql
```

**This creates:**
- ✅ `books` table (with price column)
- ✅ `users` table
- ✅ `orders` table (for purchases)
- ✅ All other supporting tables

### 2️⃣ Books Must Exist in Catalog
Books must have:
- ✅ `price` > 0 (e.g., 15.99)
- ✅ `available` > 0 (e.g., 10 copies in stock)
- ✅ `for_sale` = 1 (enabled for purchase)

**Add books via:**
- Admin Panel: Log in as admin → Books → Add Book
- SQL: `INSERT INTO books (...) VALUES (...);`

### 3️⃣ User Must Be Logged In
- Register at `/register` or
- Login at `/login` with email and password

---

## The Purchase Process (Step-by-Step)

### Step 1: User Visits Application
```
http://localhost/library-app/public/
```

### Step 2: User Logs In
```
Home → Click "Login"
Enter email: user@example.com
Enter password: yourpassword
Click "Sign In"
```

### Step 3: User Browses Books
```
Navigation → "Browse Books" or Search
Can see: Title, Author, Rating, Price, Available Stock
```

### Step 4: User Views Book Details
```
Click Book Title
See:
  - Title, Author, Description
  - Price: $15.99 ← Must be > 0
  - Available: 10 copies ← Must be > 0
```

### Step 5: User Initiates Purchase
```
On Book Detail Page
Click "Buy Now" button
```

### Step 6: User Sees Checkout Page
```
See:
  - Book title and price
  - Quantity selector (default: 1)
  - Total price calculation
  - Payment method options
```

### Step 7: User Completes Purchase
```
Select Quantity: e.g., 2 copies
Select Payment Method:
  • Credit Card
  • PayPal
  • Bank Transfer

Click "Complete Purchase"
```

### Step 8: Purchase Processed
```
✅ Order Created
   - Order record saved to database
   - Order ID generated

✅ Stock Updated
   - Book's available count decreases
   - From 10 → 8 (if bought 2 copies)

✅ Success Message
   "Payment processed successfully"

✅ Redirect to Orders
   User redirected to "My Orders" page
```

### Step 9: User Sees Their Order
```
My Profile → My Orders
Shows:
  - Order ID
  - Book Title
  - Quantity: 2
  - Total Price: $31.98
  - Status: Paid ✓
  - Date: Today's date
```

---

## Database Operations During Purchase

### Before Purchase
```sql
-- Book exists with stock
SELECT id, title, available, price FROM books WHERE id = 1;
-- Result: id=1, title="The Hobbit", available=10, price=15.99

-- No orders yet for this user
SELECT * FROM orders WHERE user_id = 1 AND book_id = 1;
-- Result: Empty
```

### During Purchase (Transaction)
```
1. START TRANSACTION
2. INSERT INTO orders (user_id, book_id, quantity, unit_price, total_price, status)
3. UPDATE books SET available = available - ? WHERE id = ?
4. COMMIT TRANSACTION
```

### After Purchase
```sql
-- Book stock decreased
SELECT id, title, available, price FROM books WHERE id = 1;
-- Result: id=1, title="The Hobbit", available=8, price=15.99

-- Order created
SELECT * FROM orders WHERE user_id = 1 AND book_id = 1 ORDER BY id DESC;
-- Result: id=1, user_id=1, book_id=1, quantity=2, unit_price=15.99, total_price=31.98, status="paid"
```

---

## What Makes a Purchase Successful ✅

### Book Conditions
```
✅ Book exists in database
✅ Book has a category
✅ Book has a price > 0
✅ Book has available stock > 0
✅ Book's for_sale flag = 1
```

### User Conditions
```
✅ User must be logged in
✅ User must have valid ID in users table
✅ User can be member or admin
```

### Transaction Conditions
```
✅ Orders table exists (created by migration)
✅ Book's available count > requested quantity
✅ No database constraints violated
✅ Foreign keys valid (user_id and book_id)
✅ Database transaction commits successfully
```

---

## How It Works Internally

### PaymentController::process() Flow
```php
1. Validate CSRF token
2. Get book_id and quantity from POST
3. Fetch book from database
4. Check stock availability
5. Get or calculate price
6. Process payment (mock)
7. START DATABASE TRANSACTION:
   a) Create order record
   b) Update book availability
   c) COMMIT transaction
8. Return success response
```

### What Gets Logged
```
[timestamp] Payment processing started | {user_id, book_id, quantity}
[timestamp] Book retrieved | {title, available, price}
[timestamp] Payment gateway successful | {transaction_id}
[timestamp] Transaction started | {}
[timestamp] Order created successfully | {order_id}
[timestamp] Book availability updated | {rows_affected}
[timestamp] Transaction committed successfully | {order_id}
```

---

## Complete Test Scenario

### 1. Set Up Database
```bash
mysql -u root luminara_library < db/luminara_library_FULL.sql
```

### 2. Add Test Book
```sql
INSERT INTO books 
  (title, author, description, pages, rating, price, copies, available, featured, for_sale)
VALUES 
  ('Test Book', 'Test Author', 'Test Description', 200, 4.5, 19.99, 5, 5, 1, 1);
```

### 3. Create Test User
```sql
INSERT INTO users (name, email, password, role, is_active)
VALUES ('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1);
-- Password is "password"
```

### 4. In Web Browser
- Go to: http://localhost/library-app/public/
- Click: Login
- Enter: test@example.com / password
- Click: Browse Books
- Find: Test Book
- Click: Buy Now
- Select: Quantity 2
- Choose: Card payment
- Click: Complete Purchase

### 5. Verify Purchase
- Go to: My Orders
- Should see: Test Book, Quantity 2, Status "Paid"

### 6. Verify in Database
```sql
-- Check order was created
SELECT * FROM orders WHERE user_id = 1 ORDER BY id DESC LIMIT 1;

-- Check book stock decreased
SELECT id, title, available FROM books WHERE title = 'Test Book';
-- Should show available = 3 (was 5, bought 2)
```

### 7. Check Logs
```
C:\xampp\htdocs\library-app\storage\logs\payment-2026-05-22.log
Should contain: "Transaction committed successfully"
```

---

## Troubleshooting Guide

### Issue: "Table 'orders' doesn't exist"
**Cause**: Migration not run
**Fix**: 
```bash
mysql -u root luminara_library < db/migrations/2026_05_21_content_and_orders.sql
```

### Issue: "Unknown column 'price'"
**Cause**: Price migration not run
**Fix**:
```bash
mysql -u root luminara_library < db/migrations/2026_05_22_add_price_and_reset_tokens.sql
```

### Issue: "Not enough stock for this quantity"
**Cause**: Book's available count is less than requested quantity
**Fix**: Update book's available count:
```sql
UPDATE books SET available = 10 WHERE id = 1;
```

### Issue: "This book cannot be purchased right now"
**Cause**: Book has no price (price = 0)
**Fix**: Set the price:
```sql
UPDATE books SET price = 15.99, for_sale = 1 WHERE id = 1;
```

### Issue: "Not logged in" or redirects to login
**Cause**: User session expired or not authenticated
**Fix**: Log in again at `/login`

### Issue: No error message shown
**Check Logs**:
```
C:\xampp\htdocs\library-app\storage\logs\payment-2026-05-22.log
```

---

## Key Configuration Files

| File | Purpose |
|------|---------|
| `config/config.php` | Database connection settings |
| `app/Controllers/PaymentController.php` | Handles purchases |
| `app/Models/Order.php` | Creates order records |
| `app/Models/Book.php` | Updates book availability |
| `db/luminara_library_FULL.sql` | Complete database schema |
| `db/migrations/` | Incremental schema updates |

---

## Success Checklist ✅

- [ ] Database created: `luminara_library`
- [ ] All tables exist (books, users, orders, etc.)
- [ ] Test book added with price > 0 and available > 0
- [ ] Test user created
- [ ] Can log in successfully
- [ ] Can browse books
- [ ] Can click "Buy Now" on a book
- [ ] See checkout page
- [ ] Can select payment method
- [ ] Can click "Complete Purchase"
- [ ] See "Payment processed successfully" message
- [ ] Redirected to "My Orders"
- [ ] Order appears in My Orders with status "Paid"
- [ ] Book stock decreased in database
- [ ] No errors in `storage/logs/payment-*.log`

---

## Support Resources

| Document | Purpose |
|----------|---------|
| `HOW_TO_BUY_BOOK.md` | Detailed step-by-step guide |
| `QUICK_SETUP.md` | 1-minute setup with commands |
| `LOGGING_GUIDE.md` | Comprehensive logging reference |
| `TROUBLESHOOTING_QUICK_REF.md` | Common errors and fixes |
| `DATABASE_MIGRATIONS.md` | Database schema information |

---

## Summary

✅ **To buy a book successfully:**

1. Ensure database is set up with all tables
2. Add books with prices and stock
3. User logs in
4. Browse and select a book
5. Click "Buy Now"
6. Complete payment
7. Order created and stock decreases
8. Success!

**The detailed logging system will capture every step and help debug any issues.**


