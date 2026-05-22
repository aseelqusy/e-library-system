# 🎯 How to Successfully Buy a Book - Visual Summary

## Your Question Answered

**"How can a user buy a book successfully?"**

### Answer: 5 Simple Steps

```
1. SET UP DATABASE          2. ADD BOOKS              3. LOG IN USER
   └─ Run migrations           └─ Add price & stock      └─ Register/Login
   
4. BROWSE & BUY             5. VERIFY SUCCESS
   └─ Click "Buy Now"          └─ Check My Orders
```

---

## 🎬 Complete User Journey

```
┌──────────────┐
│   HOME PAGE  │
│              │
│ • Browse     │
│ • Register   │
│ • Login      │
└──────────────┘
       ↓
       │ User clicks "Register"
       ↓
┌──────────────┐
│  REGISTER    │
│              │
│ Name:  ___   │
│ Email: ___   │
│ Pass:  ___   │
└──────────────┘
       ↓
       │ User logs in
       ↓
┌──────────────┐
│  LOGGED IN   │ ✓
└──────────────┘
       ↓
       │ User clicks "Browse Books"
       ↓
┌──────────────────────┐
│  BOOK CATALOG        │
│                      │
│ The Hobbit $15.99    │ ← price > 0
│   Stock: 10 copies   │ ← available > 0
│   [Buy Now] button   │
└──────────────────────┘
       ↓
       │ User clicks "Buy Now"
       ↓
┌──────────────────────┐
│  CHECKOUT PAGE       │
│                      │
│ Title: The Hobbit    │
│ Price: $15.99        │
│ Quantity: [2]        │
│ Total: $31.98        │
│ Payment: [Card v]    │
│ [Complete Purchase]  │
└──────────────────────┘
       ↓
       │ User clicks "Complete Purchase"
       ↓
┌──────────────────────┐
│  PROCESSING...       │
│                      │
│ ✓ Validate book     │
│ ✓ Check stock       │
│ ✓ Process payment   │
│ ✓ Create order      │
│ ✓ Update stock      │
└──────────────────────┘
       ↓
       │ Success!
       ↓
┌──────────────────────┐
│  SUCCESS MESSAGE     │
│                      │
│ ✓ Payment processed  │
│   successfully!      │
│                      │
│ [View My Orders >]   │
└──────────────────────┘
       ↓
       │ User clicks "View My Orders" or navigates there
       ↓
┌──────────────────────┐
│  MY ORDERS           │
│                      │
│ Order #1             │
│ • Book: The Hobbit   │
│ • Quantity: 2        │
│ • Total: $31.98      │
│ • Status: ✓ Paid     │
│ • Date: Today        │
└──────────────────────┘
       ↓
       │
      ✅ PURCHASE COMPLETE!
       │
```

---

## 📋 What Must Exist

### ✅ Database Tables
```
✓ books
  ├─ id
  ├─ title
  ├─ author
  ├─ price (MUST be > 0)
  ├─ available (MUST be > 0)
  ├─ for_sale (MUST be 1)
  └─ ... other fields ...
  
✓ users
  ├─ id
  ├─ name
  ├─ email
  ├─ password
  └─ role
  
✓ orders (NEW - created by migration)
  ├─ id
  ├─ user_id
  ├─ book_id
  ├─ quantity
  ├─ unit_price
  ├─ total_price
  ├─ status
  └─ created_at
```

### ✅ Books in Catalog
```sql
-- Example book that can be purchased
SELECT * FROM books WHERE id = 1;

Result must show:
- title = "The Hobbit"
- price = 15.99  ✓ (> 0)
- available = 10 ✓ (> 0)
- for_sale = 1   ✓ (enabled)
```

### ✅ Users in System
```sql
-- Example user that can purchase
SELECT * FROM users WHERE id = 1;

Result must show:
- name = "Test User"
- email = "user@example.com"
- password = (hashed)
- role = "member"
- is_active = 1
```

---

## 🔧 Setup Commands

### 1️⃣ Create Database
```bash
mysql -u root -e "CREATE DATABASE luminara_library CHARACTER SET utf8mb4"
```

### 2️⃣ Load Full Schema
```bash
mysql -u root luminara_library < db/luminara_library_FULL.sql
```

### 3️⃣ Add Test Book
```sql
INSERT INTO books (title, author, price, available, copies, for_sale)
VALUES ('The Hobbit', 'J.R.R. Tolkien', 15.99, 10, 10, 1);
```

### 4️⃣ Create Test User
```sql
INSERT INTO users (name, email, password, role, is_active)
VALUES ('Test User', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 1);
-- Password: "password"
```

### 5️⃣ Verify Before Purchase
```sql
-- Check book
SELECT id, title, price, available FROM books WHERE id = 1;

-- Check user
SELECT id, name, email FROM users WHERE id = 1;

-- Check orders table exists
DESCRIBE orders;
```

---

## 🎯 Purchase Checklist

Before clicking "Buy Now":
- [ ] Database created
- [ ] Tables exist (books, users, orders)
- [ ] Test book added with price > 0
- [ ] Book has available stock > 0
- [ ] User account created
- [ ] User is logged in
- [ ] User can see book in catalog

During purchase:
- [ ] Click "Buy Now"
- [ ] Select quantity (1-10)
- [ ] Choose payment method
- [ ] Click "Complete Purchase"

After purchase:
- [ ] See "Payment processed successfully"
- [ ] Check "My Orders" shows the order
- [ ] Verify status = "Paid" ✓
- [ ] Check log has no errors
- [ ] Verify stock decreased by quantity

---

## 📊 Database State Changes

### BEFORE Purchase
```
Books Table:
┌────┬──────────┬───────┬──────────┐
│ id │ title    │ price │ available│
├────┼──────────┼───────┼──────────┤
│ 1  │ Hobbit   │ 15.99 │   10     │
└────┴──────────┴───────┴──────────┘

Orders Table:
(empty)
```

### AFTER Buying 2 Copies
```
Books Table:
┌────┬──────────┬───────┬──────────┐
│ id │ title    │ price │ available│  ← Changed!
├────┼──────────┼───────┼──────────┤
│ 1  │ Hobbit   │ 15.99 │    8     │  ← Was 10
└────┴──────────┴───────┴──────────┘

Orders Table:
┌────┬─────────┬─────────┬──────────┬────────┐
│ id │ user_id │ book_id │ quantity │ total  │  ← NEW!
├────┼─────────┼─────────┼──────────┼────────┤
│ 1  │    1    │    1    │    2     │ 31.98  │
└────┴─────────┴─────────┴──────────┴────────┘
```

---

## 🔍 How to Verify Success

### ✅ Sign 1: UI Shows Success
```
Message: "Payment processed successfully"
Redirect: My Orders page
Order visible: With status "Paid"
```

### ✅ Sign 2: Database Changed
```sql
-- Stock decreased
SELECT available FROM books WHERE id = 1;
-- Should show: 8 (was 10, bought 2)

-- Order created
SELECT * FROM orders WHERE id = 1;
-- Should show new order with status 'paid'
```

### ✅ Sign 3: Logs Show Success
```
C:\xampp\htdocs\library-app\storage\logs\payment-2026-05-22.log

Contains:
✓ "Payment processing started"
✓ "Book retrieved"
✓ "Payment gateway successful"
✓ "Transaction committed successfully"

NO "ERROR:" entries
```

---

## ❌ If Something Fails

### Step 1: Check Logs
```powershell
Get-Content "C:\xampp\htdocs\library-app\storage\logs\payment-*.log" -Tail 30
```

### Step 2: Look for ERROR
```
[timestamp] ERROR: Order creation failed
  Exception: PDOException
  Message: Table 'orders' doesn't exist
```

### Step 3: Fix It
```bash
# Run migration to create orders table
mysql -u root luminara_library < db/migrations/2026_05_21_content_and_orders.sql
```

### Step 4: Try Again
- Clear logs
- Attempt purchase again
- Check logs for success

---

## 💡 Pro Tips

### Tip 1: Always Check Books Before Purchase
```sql
SELECT id, title, price, available FROM books 
WHERE price > 0 AND available > 0;
```

### Tip 2: Use Mock Payments
- Card, PayPal, and Bank Transfer are all mock (fake)
- No real money involved
- Always succeeds in testing

### Tip 3: Check Logs First
When things fail, **always check the logs first**:
```
C:\xampp\htdocs\library-app\storage\logs\payment-2026-05-22.log
```

### Tip 4: Admin Account
```
Email: admin@example.com
Password: password (or changed by you)
```

---

## 📚 Complete Example

### Start to Finish

```bash
# 1. Set up database
mysql -u root luminara_library < db/luminara_library_FULL.sql

# 2. Add test book and user
mysql -u root -e "
  INSERT INTO luminara_library.books 
    (title, author, price, available, copies, for_sale)
  VALUES ('The Hobbit', 'J.R.R. Tolkien', 15.99, 10, 10, 1);

  INSERT INTO luminara_library.users 
    (name, email, password, role, is_active)
  VALUES ('Test User', 'user@example.com', 
    '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'member', 1);
"
```

```
3. In Browser:
   • Go to: http://localhost/library-app/public/
   • Click: Login
   • Email: user@example.com
   • Password: password
   • Click: Sign In
   
4. Browse:
   • Click: Browse Books
   • Find: The Hobbit
   • Click: Buy Now
   
5. Checkout:
   • Quantity: 2
   • Payment: Card
   • Click: Complete Purchase
   
6. Verify:
   • See: "Payment processed successfully"
   • Go to: My Orders
   • Check: Order appears with status "Paid"
   
✅ DONE!
```

---

## 🎓 Key Takeaway

**To buy a book successfully:**

1. **Database** must be set up with orders table
2. **Book** must have price > 0 and available > 0
3. **User** must be logged in
4. **Click** "Buy Now" on the book
5. **Complete** the checkout
6. **Verify** the order appears in My Orders

**That's it! 🎉**

If anything fails, check the logs at `storage/logs/` for the exact error message.


