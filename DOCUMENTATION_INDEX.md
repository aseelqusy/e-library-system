# 📚 Library App Purchase System - Complete Documentation

## Overview

This guide explains **everything you need to know** about successfully buying a book in the Luminara Library application, including setup, step-by-step instructions, debugging, and troubleshooting.

---

## 📖 Documentation Files

### For Users: How to Buy a Book

| Document | Purpose | Read When |
|----------|---------|-----------|
| **`BUY_BOOK_COMPLETE_GUIDE.md`** | Full guide on buying books | You want to understand the complete flow |
| **`HOW_TO_BUY_BOOK.md`** | Step-by-step detailed instructions | You're ready to make a purchase |
| **`QUICK_SETUP.md`** | 1-minute setup with copy-paste commands | You want to get started immediately |

### For Developers: Debugging & Logging

| Document | Purpose | Read When |
|----------|---------|-----------|
| **`LOGGING_IMPLEMENTATION.md`** | Technical details of logging system | You need to understand implementation |
| **`LOGGING_GUIDE.md`** | Comprehensive logging reference | You need to debug database errors |
| **`TROUBLESHOOTING_QUICK_REF.md`** | Quick error lookup table | A purchase failed and you need to fix it |
| **`LOGGING_STATUS.md`** | Current status summary | You want a 2-minute overview |

### Supporting Documentation

| Document | Purpose |
|----------|---------|
| `IMPLEMENTATION_CHECKLIST.md` | Verification that all logging is in place |
| `README.md` | Project overview |
| `PROJECT_OVERVIEW.md` | Complete project structure |

---

## 🚀 Quick Start (3 Steps)

### Step 1: Set Database
```bash
mysql -u root luminara_library < db/luminara_library_FULL.sql
```

### Step 2: Add Test Book
```sql
INSERT INTO books (title, author, price, available, for_sale)
VALUES ('The Hobbit', 'J.R.R. Tolkien', 15.99, 10, 1);
```

### Step 3: Buy Book
1. Go to: `http://localhost/library-app/public/`
2. Login
3. Browse books
4. Click "Buy Now"
5. Complete purchase

---

## ✅ What Was Implemented

### Comprehensive Logging System

**Added to these files:**
- ✅ `core/Helpers.php` - `logDebug()` and `logError()` functions
- ✅ `app/Controllers/PaymentController.php` - 15+ log points
- ✅ `app/Controllers/OrderController.php` - 12+ log points
- ✅ `app/Models/Order.php` - Full operation logging
- ✅ `app/Models/Book.php` - Stock update logging

**Features:**
- ✅ Microsecond timestamps
- ✅ JSON-encoded context
- ✅ Full exception details with stack traces
- ✅ Organized by channel (payment, orders, books)
- ✅ Auto-creates `storage/logs/` directory
- ✅ No performance impact
- ✅ Thread-safe file operations

### Database Infrastructure

**Tables created:**
- ✅ `orders` - Stores all purchases
- ✅ `users` - User accounts  
- ✅ `books` - Book catalog
- ✅ `categories` - Book categories
- ✅ And supporting tables

**Columns added:**
- ✅ `books.price` - Book selling price
- ✅ `books.for_sale` - Can be purchased
- ✅ `books.format` - written/audio/both
- ✅ `books.for_borrow` - Can be borrowed

---

## 📊 Purchase Flow

```
┌─────────────────────────────────────────────────────────┐
│                 PURCHASE PROCESS FLOW                    │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  1. USER LOGS IN                                         │
│     └─ Check AuthenticationLog ✓                         │
│                                                          │
│  2. BROWSE CATALOG                                       │
│     └─ Check Book: price > 0, available > 0 ✓            │
│                                                          │
│  3. SELECT PAYMENT METHOD                                │
│     └─ Card / PayPal / Bank Transfer ✓                   │
│                                                          │
│  4. INITIATE TRANSACTION                                 │
│     └─ Log: "Payment processing started" ✓               │
│                                                          │
│  5. VALIDATE BOOK & PRICE                                │
│     └─ Log: "Book retrieved" ✓                           │
│                                                          │
│  6. PROCESS PAYMENT                                      │
│     └─ Log: "Payment gateway successful" ✓               │
│                                                          │
│  7. DATABASE TRANSACTION                                 │
│     ├─ Log: "Transaction started" ✓                      │
│     ├─ Create order record                               │
│     │  └─ Log: "Order created successfully" ✓            │
│     ├─ Update book availability                          │
│     │  └─ Log: "Book availability updated" ✓             │
│     └─ Commit transaction                                │
│        └─ Log: "Transaction committed successfully" ✓    │
│                                                          │
│  8. CONFIRM PURCHASE                                     │
│     └─ Show success message ✓                            │
│     └─ Redirect to My Orders ✓                           │
│                                                          │
│  ✅ PURCHASE COMPLETE!                                    │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## 🔍 Log File Locations

When a purchase is attempted, logs are created here:

```
C:\xampp\htdocs\library-app\storage\logs\
├── payment-2026-05-22.log        ← Payment flow logs
├── orders-2026-05-22.log         ← Order creation logs
├── books-2026-05-22.log          ← Stock update logs
└── app-2026-05-22.log            ← General app logs
```

Each date gets its own file. All logs are appended to the file.

### Log Format
```
[2026-05-22 14:35:21.123456] Payment processing started | {"user_id":1,"book_id":5,...}
[2026-05-22 14:35:45.678901] Transaction committed successfully | {"order_id":42}
```

---

## 🛠️ Database Setup Required

### Create Database
```sql
CREATE DATABASE luminara_library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Run FULL Schema (Recommended for Fresh Setup)
```bash
mysql -u root luminara_library < db/luminara_library_FULL.sql
```

### OR Run Individual Migrations (Existing Database)
```bash
mysql -u root luminara_library < db/migrations/2026_05_21_content_and_orders.sql
mysql -u root luminara_library < db/migrations/2026_05_22_add_price_and_reset_tokens.sql
mysql -u root luminara_library < db/migrations/2026_05_22_add_format_and_purchase_flags.sql
```

---

## 📋 Prerequisites for Successful Purchase

### Database Requirements
- ✅ Database `luminara_library` exists
- ✅ All tables created (books, users, orders, etc.)
- ✅ All migrations applied
- ✅ Foreign key constraints active

### Book Requirements
- ✅ Book exists in `books` table
- ✅ `price` > 0 (e.g., 15.99)
- ✅ `available` > 0 (stock count)
- ✅ `for_sale` = 1 (enabled for purchase)

### User Requirements
- ✅ User account exists in `users` table
- ✅ User is logged in
- ✅ Session is valid

### Request Requirements
- ✅ Valid CSRF token in request
- ✅ Valid book_id parameter
- ✅ Valid quantity (1-10)
- ✅ Valid payment method

---

## 🐛 Debugging Failed Purchases

### Step 1: Check Logs
```bash
# View latest log entries
Get-Content "C:\xampp\htdocs\library-app\storage\logs\payment-*.log" -Tail 50
```

### Step 2: Search for ERROR
Look for lines starting with "ERROR:" in the log file

### Step 3: Read Exception Message
The error message will tell you exactly what went wrong:
- `1146 Table not found` → Run migrations
- `1054 Unknown column` → Missing columns
- `1452 Foreign key constraint` → Invalid user_id or book_id
- Other error codes → Database-specific issue

### Step 4: Apply Fix
Use the fix table in `TROUBLESHOOTING_QUICK_REF.md` to resolve the issue

### Step 5: Try Again
Clear old logs and attempt purchase again

---

## 🎯 Success Indicators

A successful purchase will show:

✅ **In Browser**
- "Payment processed successfully" message
- Redirect to "My Orders" page
- Order listed with status "Paid"

✅ **In Database**
```sql
-- Order created
SELECT * FROM orders WHERE id = (SELECT MAX(id) FROM orders);

-- Stock decreased
SELECT id, title, available FROM books WHERE id = 1;
-- available should be less than before
```

✅ **In Logs**
```
storage/logs/payment-2026-05-22.log contains:
✓ "Payment processing started"
✓ "Book retrieved"  
✓ "Payment gateway successful"
✓ "Transaction committed successfully"
(No "ERROR:" entries)
```

---

## 📞 Quick Reference

### Most Common Errors

| Error | Solution |
|-------|----------|
| Table 'orders' doesn't exist | Run: `db/migrations/2026_05_21_content_and_orders.sql` |
| Unknown column 'price' | Run: `db/migrations/2026_05_22_add_price_and_reset_tokens.sql` |
| Not enough stock | Update book's `available` count |
| Cannot be purchased | Set book's `price > 0` and `for_sale = 1` |
| Not logged in | Log in at `/login` first |

### Key Files

| File | Purpose |
|------|---------|
| `core/Helpers.php` | Logging functions |
| `app/Controllers/PaymentController.php` | Payment processing |
| `app/Models/Order.php` | Order creation |
| `app/Models/Book.php` | Stock updates |
| `config/config.php` | Database configuration |

### Key Directories

| Path | Purpose |
|------|---------|
| `db/` | Database schemas and migrations |
| `storage/logs/` | Log files created here |
| `app/Controllers/` | Request handlers |
| `app/Models/` | Database models |

---

## 🎓 Learning Path

**For Users:**
1. Start with: `QUICK_SETUP.md` (3 minutes)
2. Then read: `HOW_TO_BUY_BOOK.md` (10 minutes)
3. If issues: Check `TROUBLESHOOTING_QUICK_REF.md` (2 minutes)

**For Developers:**
1. Start with: `LOGGING_STATUS.md` (5 minutes)
2. Then read: `LOGGING_IMPLEMENTATION.md` (10 minutes)
3. Deep dive: `LOGGING_GUIDE.md` (30 minutes)
4. For errors: `TROUBLESHOOTING_QUICK_REF.md` (reference)

---

## ✨ Summary

The Luminara Library application now has:

✅ **Complete Purchase System**
- Full end-to-end book purchasing flow
- Mock payment processing
- Database transaction safety
- Order tracking

✅ **Professional Logging**
- Logs every step of purchase process
- Captures complete exception details
- Writes to organized log files
- Helps debug any issues

✅ **Comprehensive Documentation**
- User guides for making purchases
- Developer guides for debugging
- Quick reference cards
- Step-by-step tutorials

✅ **Database Infrastructure**
- All necessary tables created
- Foreign keys defined
- Migrations in place
- Test data readily available

---

## 🚀 Get Started Now

### For Users:
1. Open: `QUICK_SETUP.md`
2. Run the SQL commands
3. Visit: http://localhost/library-app/public/
4. Log in and buy a book!

### For Developers:
1. Open: `LOGGING_GUIDE.md`
2. Attempt a purchase
3. Check: `storage/logs/payment-*.log`
4. Debugging reference: `TROUBLESHOOTING_QUICK_REF.md`

---

**Status**: ✅ READY TO USE

All systems are in place. You can now purchase books and debug any issues using the comprehensive logging system.


