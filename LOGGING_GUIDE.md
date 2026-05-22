# Enhanced Logging Guide

## Overview
Complete detailed logging has been added to trace the entire book purchase flow and identify database errors. All logs are written to timestamped files in `storage/logs/`.

## Log Files
Logs are organized by channel:
- **`payment-YYYY-MM-DD.log`** - Payment processing flow (PaymentController)
- **`orders-YYYY-MM-DD.log`** - Order creation flow (OrderController, Order model)
- **`books-YYYY-MM-DD.log`** - Book availability updates

## What's Being Logged

### Payment Flow (PaymentController::process)
```
1. Payment processing started
   - user_id, book_id, quantity, payment_method
   
2. Book retrieved
   - title, available stock, price
   
3. Price calculation (if needed)
   - resolved unit_price from formula
   
4. Payment gateway processing
   - total amount, payment method
   - transaction_id on success
   
5. Order creation
   - Order record insertion
   - Book availability adjustment
   - Transaction commit/rollback
   
6. Error capture (if any)
   - Full exception details
   - Stack trace
   - Context data
```

### Order Flow (OrderController::buy)
```
1. Order request validation
   - user_id, book_id, quantity
   
2. Book stock validation
   - requested vs available
   
3. Order creation
   - Unit price resolution
   - Database transaction start
   - Order insert
   - Stock deduction
   - Transaction commit
   
4. Error capture (if any)
   - Exception type, message, code
   - File and line number
   - Full stack trace
```

### Database Operations (Order model)
```
1. ensureTable() - Table creation check
   - Logs table existence verification
   - Logs any table creation errors
   
2. create() - Order insertion
   - Logs SQL parameter values
   - Logs execution result
   - Logs insert errors or success
   - Logs generated order_id
   
3. adjustAvailable() - Stock update
   - Logs book_id and delta
   - Logs execution result
   - Logs rows affected
   - Logs update errors
```

## How to Use Logs to Debug Purchase Issues

### Step 1: Attempt a Purchase
1. Go to a book page in the application
2. Try to purchase (click buy/checkout button)
3. Note if you get an error message

### Step 2: Check the Logs
Navigate to `C:\xampp\htdocs\library-app\storage\logs\` and open the latest:
- `payment-YYYY-MM-DD.log` (for /payment/process endpoint)
- `orders-YYYY-MM-DD.log` (for /order/buy endpoint)
- `books-YYYY-MM-DD.log` (for stock updates)

### Step 3: Understand the Log Format
Each log line contains:
```
[YYYY-MM-DD HH:MM:SS.microseconds] MESSAGE | Context JSON or Exception Details
```

Example:
```json
[2026-05-22 14:30:45.123456] Payment processing started | {"user_id":1,"book_id":5,"quantity":1,"payment_method":"card"}
[2026-05-22 14:30:45.456789] Book retrieved | {"book_id":5,"title":"Test Book","available":10,"price":15.99}
[2026-05-22 14:30:46.789012] ERROR: Order creation failed during transaction | {"user_id":1,"book_id":5,"quantity":1}
  Exception: PDOException
  Message: SQLSTATE[HY000]: General error: 1030 Got error 28 from storage engine
  Code: HY000
  File: /app/Models/Order.php:45
  Trace: ...
```

### Step 4: Identify the Issue
Look for patterns:

#### Issue: "Table doesn't exist"
```
ERROR: Order::ensureTable() failed
  Message: SQLSTATE[HY000]: General error: 1146 Table 'luminara_library.orders' doesn't exist
```
**Fix**: Run database migration scripts

#### Issue: "Foreign key constraint fails"
```
ERROR: Order::create() failed
  Message: SQLSTATE[HY000]: General error: 1452 Cannot add or update a child row: a foreign key constraint fails
```
**Fix**: Ensure user_id and book_id exist in users and books tables

#### Issue: "Column doesn't exist"
```
ERROR: Order insert statement execute failed
  error_info: {"0":"HY000","1":1054,"2":"Unknown column 'field_name' in 'field list'"}
```
**Fix**: Run ALTER TABLE migrations to add missing columns

#### Issue: "Insufficient privileges"
```
ERROR: Order::ensureTable() failed
  Message: SQLSTATE[HY000]: General error: 1040 Too many connections / 1227 Access denied
```
**Fix**: Check database user permissions

## Log Entry Examples

### Successful Purchase
```
[2026-05-22 14:30:45.123456] Payment processing started | {"user_id":1,"book_id":5,"quantity":1,"payment_method":"card"}
[2026-05-22 14:30:45.234567] Book retrieved | {"book_id":5,"title":"The Hobbit","available":10,"price":15.99}
[2026-05-22 14:30:45.345678] Processing payment gateway | {"book_id":5,"quantity":1,"unit_price":15.99,"total":15.99,"payment_method":"card"}
[2026-05-22 14:30:45.456789] Payment gateway successful | {"transaction_id":"TXN-abc123def456"}
[2026-05-22 14:30:45.567890] Transaction started | {}
[2026-05-22 14:30:45.678901] Creating order record | {"user_id":1,"book_id":5,"quantity":1,"unit_price":15.99,"total_price":15.99}
[2026-05-22 14:30:45.789012] Order::create() called | {"user_id":1,"book_id":5,"quantity":1,"unit_price":15.99,"total_price":15.99,"status":"paid"}
[2026-05-22 14:30:45.890123] Executing order insert statement | {"user_id":1,"book_id":5,"quantity":1,"unit_price":15.99,"total_price":15.99,"status":"paid"}
[2026-05-22 14:30:45.901234] Order inserted successfully | {"order_id":42,"user_id":1}
[2026-05-22 14:30:46.012345] Adjusting book availability | {"book_id":5,"delta":-1}
[2026-05-22 14:30:46.123456] Book::adjustAvailable() called | {"book_id":5,"delta":-1}
[2026-05-22 14:30:46.234567] Book availability updated successfully | {"book_id":5,"delta":-1,"rows_affected":1}
[2026-05-22 14:30:46.345678] Transaction committed successfully | {"order_id":42}
```

### Failed Purchase - Missing Column
```
[2026-05-22 14:30:45.123456] Payment processing started | {"user_id":1,"book_id":5,"quantity":1,"payment_method":"card"}
[2026-05-22 14:30:45.234567] Book retrieved | {"book_id":5,"title":"The Hobbit","available":10,"price":0}
[2026-05-22 14:30:45.345678] Price calculated from formula | {"book_id":5,"unit_price":14.45}
[2026-05-22 14:30:45.456789] Processing payment gateway | {"book_id":5,"quantity":1,"unit_price":14.45,"total":14.45,"payment_method":"card"}
[2026-05-22 14:30:45.567890] Payment gateway successful | {"transaction_id":"TXN-xyz789"}
[2026-05-22 14:30:45.678901] Transaction started | {}
[2026-05-22 14:30:45.789012] Creating order record | {"user_id":1,"book_id":5,"quantity":1,"unit_price":14.45,"total_price":14.45}
[2026-05-22 14:30:45.890123] Order::create() called | {"user_id":1,"book_id":5,"quantity":1,"unit_price":14.45,"total_price":14.45,"status":"paid"}
[2026-05-22 14:30:45.901234] Executing order insert statement | {"user_id":1,"book_id":5,"quantity":1,"unit_price":14.45,"total_price":14.45,"status":"paid"}
[2026-05-22 14:30:46.012345] ERROR: Order creation failed during transaction | Context: {"user_id":1,"book_id":5,"quantity":1}
  Exception: PDOException
  Message: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'total_price' in 'field list'
  Code: HY000
  File: C:\xampp\htdocs\library-app\public\index.php:123
  Trace: #0 [internal function]: PDOStatement->execute()
         #1 C:\xampp\htdocs\library-app\app\Models\Order.php:45
         #2 C:\xampp\htdocs\library-app\app\Controllers\PaymentController.php:124
  ...
```

## Key Information from Logs

When analyzing a failed purchase, look for:

1. **Transaction Status**
   - "Transaction started" - Transaction was initiated
   - "Transaction committed successfully" - Success
   - "Transaction rolled back" - Failure and rollback occurred

2. **Exact Failure Point**
   - Search for "ERROR" to find the exact failure
   - Look at the exception message for database-specific errors
   - Check the file and line number to see where it failed

3. **Database Error Codes**
   - `1054` - Unknown column
   - `1146` - Table doesn't exist
   - `1452` - Foreign key constraint fails
   - `1064` - SQL syntax error
   - `1030` - Storage engine error
   - `2006` - MySQL server has gone away

4. **Parameter Values**
   - All input parameters are logged
   - Check if values are correct and in expected format
   - Verify data types (int, float, string)

## Viewing Logs in Real-Time

### Option 1: File Explorer
1. Navigate to `C:\xampp\htdocs\library-app\storage\logs\`
2. Open `payment-YYYY-MM-DD.log` or `orders-YYYY-MM-DD.log`
3. View in any text editor

### Option 2: Command Line (PowerShell)
```powershell
# View latest log entries
Get-Content "C:\xampp\htdocs\library-app\storage\logs\payment-*.log" -Tail 20

# Follow log in real-time (like tail -f)
Get-Content "C:\xampp\htdocs\library-app\storage\logs\payment-2026-05-22.log" -Tail 10 -Wait

# Search for errors
Select-String -Path "C:\xampp\htdocs\library-app\storage\logs\*.log" -Pattern "ERROR"
```

### Option 3: PHP Script to View Logs
Create a file `view_logs.php` in public folder:
```php
<?php
$logDir = dirname(__DIR__) . '/storage/logs';
$files = glob($logDir . '/payment-*.log');
arsort($files);
echo "<pre>";
if ($files) {
    echo htmlspecialchars(file_get_contents($files[0]));
}
echo "</pre>";
?>
```

## Filtering Logs

### All order-related events
```
grep -r "Order" storage/logs/
```

### All errors
```
grep -r "ERROR" storage/logs/
```

### Specific book
```
grep "book_id.:5" storage/logs/*.log
```

### Specific user
```
grep "user_id.:1" storage/logs/*.log
```

## Troubleshooting Steps

1. **Attempt purchase** → Check logs → Identify error message
2. **Search for exception type** → Find relevant fix in this guide
3. **Implement fix** → Clear old logs → Try purchase again
4. **Compare before/after logs** → Verify success

## Log Retention

Logs are written to files named by date and channel. Old logs persist for historic troubleshooting. To clean up:
```powershell
# Remove logs older than 30 days
Get-ChildItem "C:\xampp\htdocs\library-app\storage\logs\*.log" -File | Where-Object {$_.LastWriteTime -lt (Get-Date).AddDays(-30)} | Remove-Item
```

## Next Steps

After identifying the issue from the logs:
1. Document the error message
2. Check the IMPLEMENTATION_SUMMARY.md or PROJECT_OVERVIEW.md for context
3. Apply necessary database migrations
4. Verify permissions and table structure
5. Try purchase again and confirm logs show success


