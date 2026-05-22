# Purchase Flow Troubleshooting Quick Reference

## Quick Steps to Debug a Failed Purchase

### 1. Attempt Purchase
- Navigate to a book detail page
- Click "Buy Now" or similar button
- Note the error message

### 2. Find the Log
```
Location: C:\xampp\htdocs\library-app\storage\logs\
File: payment-YYYY-MM-DD.log  (for /payment/process route)
  or orders-YYYY-MM-DD.log    (for /order/buy route)
```

### 3. Common Errors & Fixes

| Error | Cause | Fix |
|-------|-------|-----|
| `1146 Table 'luminara_library.orders' doesn't exist` | Orders table not created | Run migrations in `db/` folder |
| `1054 Unknown column 'total_price'` | Column missing in orders table | Run migration `2026_05_21_content_and_orders.sql` |
| `1054 Unknown column 'price'` | Column missing in books table | Run migration `2026_05_22_add_price_and_reset_tokens.sql` |
| `1452 Cannot add a child row` | user_id or book_id doesn't exist | Check users and books tables have correct data |
| `Access denied for user 'root'@'localhost'` | Database permission issue | Check DB_USER and DB_PASS in config/config.php |
| `Collation error` | Charset mismatch | Reset database, re-run migrations with UTF-8 |
| `Got error 28 from storage engine` | Disk space full or filesystem error | Check disk space, restart MySQL |

### 4. Log Entry Format
```
[2026-05-22 14:30:45.123456] MESSAGE | {"json":"context"}
[2026-05-22 14:30:46.234567] ERROR: FAILED | Full exception details below
  Exception: ExceptionType
  Message: Error message
  File: path/to/file.php:lineNumber
```

### 5. Key Log Points for Successful Purchase

Must see these in order:
1. ✅ "Payment processing started" 
2. ✅ "Book retrieved"
3. ✅ "Payment gateway successful"
4. ✅ "Transaction started"
5. ✅ "Order created successfully"
6. ✅ "Book availability updated successfully"
7. ✅ "Transaction committed successfully"

If any step shows ERROR, that's the problem.

---

## Purchase Flow Paths

### Path 1: PaymentController::process() - Full Payment Flow
```
/payment/process (POST)
├─ CSRF validation
├─ Validate book_id, quantity
├─ Fetch book from database
├─ Check stock availability
├─ Calculate/fetch price
├─ Process payment (mock)
├─ START TRANSACTION
│  ├─ Order::create()
│  │  ├─ Order::ensureTable()
│  │  └─ INSERT into orders
│  ├─ Book::adjustAvailable()
│  │  └─ UPDATE books SET available
│  └─ COMMIT TRANSACTION
└─ Response to client
```

### Path 2: OrderController::buy() - Direct Order Path
```
/order/buy (POST)
├─ CSRF validation
├─ Validate book_id, quantity
├─ Fetch book from database
├─ Check stock availability
├─ Calculate/fetch price
├─ START TRANSACTION
│  ├─ Order::create()
│  │  ├─ Order::ensureTable()
│  │  └─ INSERT into orders
│  ├─ Book::adjustAvailable()
│  │  └─ UPDATE books SET available
│  └─ COMMIT TRANSACTION
└─ Response to client
```

---

## Direct Log Analysis Examples

### ✅ Example: Success
```
[2026-05-22 14:30:45.123456] Payment processing started | {"user_id":1,"book_id":5,"quantity":1,"payment_method":"card"}
[2026-05-22 14:30:45.234567] Book retrieved | {"book_id":5,"title":"The Hobbit","available":10,"price":15.99}
[2026-05-22 14:30:45.345678] Payment gateway successful | {"transaction_id":"TXN-abc123"}
[2026-05-22 14:30:45.456789] Transaction started | {}
[2026-05-22 14:30:45.890123] Order inserted successfully | {"order_id":42,"user_id":1}
[2026-05-22 14:30:46.234567] Book availability updated successfully | {"book_id":5,"delta":-1,"rows_affected":1}
[2026-05-22 14:30:46.345678] Transaction committed successfully | {"order_id":42}
```
→ **PURCHASE SUCCEEDED**

### ❌ Example: Failed - Missing Table
```
[2026-05-22 14:30:45.123456] Payment processing started | {"user_id":1,"book_id":5,"quantity":1,...}
[2026-05-22 14:30:45.234567] Book retrieved | {...}
[2026-05-22 14:30:45.345678] Payment gateway successful | {...}
[2026-05-22 14:30:45.456789] Transaction started | {}
[2026-05-22 14:30:45.890123] ERROR: Order::ensureTable() failed
  Exception: PDOException
  Message: SQLSTATE[HY000]: General error: 1146 Table 'luminara_library.orders' doesn't exist
```
→ **Fix**: Run one of these migration files (in order):
1. `db/migrations/2026_05_21_content_and_orders.sql`
2. Then try again

### ❌ Example: Failed - Bad Data
```
[2026-05-22 14:30:45.123456] Payment processing started | {"user_id":999,"book_id":-5,"quantity":0,...}
[2026-05-22 14:30:45.234567] Invalid purchase details | {"book_id":-5,"quantity":0}
```
→ **Fix**: Client is sending invalid data. Check book detail page form values.

### ❌ Example: Failed - Stock Issue
```
[2026-05-22 14:30:45.123456] Payment processing started | {...}
[2026-05-22 14:30:45.234567] Book retrieved | {"book_id":5,"title":"Book","available":0,...}
[2026-05-22 14:30:45.345678] Insufficient stock | {"book_id":5,"requested":1,"available":0}
```
→ **Fix**: Book out of stock. Admin needs to add more copies in admin panel.

---

## Database Checks

If you see database errors, verify:

```sql
-- Check if orders table exists
SHOW TABLES LIKE 'orders';

-- Check orders table structure
DESCRIBE orders;

-- Check if books table has price column
SHOW COLUMNS FROM books LIKE 'price';

-- Check if users table exists
SHOW TABLES LIKE 'users';

-- Check sample data
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM books;
SELECT COUNT(*) FROM orders;
```

---

## Log File Locations

| Log File | Location | Purpose |
|----------|----------|---------|
| payment-YYYY-MM-DD.log | `storage/logs/` | Payment gateway flow logs |
| orders-YYYY-MM-DD.log | `storage/logs/` | Order creation flow logs |
| books-YYYY-MM-DD.log | `storage/logs/` | Book update flow logs |

---

## Immediate Actions

1. **Attempt a purchase** → immediately go to `storage/logs/`
2. **Open latest payment or orders log file**
3. **Search for "ERROR"** if purchase failed
4. **Match error message** to "Common Errors & Fixes" table above
5. **Apply fix** → Clear logs → Try again

---

## Success Indicators in Logs

Look for these patterns:
- ✅ `"Order inserted successfully"` → DB insert worked
- ✅ `"Book availability updated successfully"` → Stock update worked
- ✅ `"Transaction committed successfully"` → Changes saved
- ✅ Response includes `"order_id"` → Order was created
  
## Failure Indicators in Logs

- ❌ `ERROR:` prefix → Indicates failure
- ❌ `Exception:` type → Which kind of exception (DB, etc.)
- ❌ `Message:` contains SQL error code → Database-specific issue
- ❌ `SQLSTATE[HY000]` → MySQL error code format
- ❌ `Transaction rolled back` → Changes were not saved

---

## PowerShell Command Cheat Sheet

```powershell
# View last 20 lines of log
Get-Content "C:\xampp\htdocs\library-app\storage\logs\payment-2026-05-22.log" -Tail 20

# Search for errors
Select-String "ERROR" "C:\xampp\htdocs\library-app\storage\logs\*.log"

# Watch log in real-time
Get-Content "C:\xampp\htdocs\library-app\storage\logs\payment-2026-05-22.log" -Tail 20 -Wait

# Get latest log file
Get-ChildItem "C:\xampp\htdocs\library-app\storage\logs\*.log" | Sort-Object LastWriteTime -Desc | Select-Object -First 1

# Delete old logs
Remove-Item "C:\xampp\htdocs\library-app\storage\logs\*.log"
```


