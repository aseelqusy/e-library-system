# Book Cover System - Testing Guide

## Pre-Deployment Testing

### 1. File Structure Verification

Before testing functionality, verify all files are in place:

```
✓ /core/Helpers.php - Enhanced with new functions
✓ /public/assets/js/book-covers.js - Client-side manager  
✓ /layouts/footer.php - Includes book-covers.js script
✓ /home/landing.php - Uses getBookCover()
✓ /catalog/book-details.php - Uses getBookCover()
✓ /catalog/browse.php - Uses getBookCover()
✓ /catalog/search.php - Uses getBookCover()
✓ /user/wishlist.php - Uses getBookCover() and displays covers
✓ /user/my-orders.php - Uses getBookCover()
✓ /payment/checkout.php - Uses getBookCover()
```

### 2. Directory Permissions

```bash
# Ensure upload directories exist and are writable
mkdir -p /path/to/public/uploads/covers
mkdir -p /path/to/public/uploads/images
chmod 755 /path/to/public/uploads/
chmod 755 /path/to/public/uploads/covers/
chmod 755 /path/to/public/uploads/images/
```

### 3. Database Verification

Check that books table has necessary columns:

```sql
-- Check books table structure
DESCRIBE books;

-- Should have:
-- - isbn (VARCHAR)
-- - cover_image (VARCHAR)
-- - cover (VARCHAR) [legacy]

-- Verify sample book with ISBN
SELECT id, title, isbn, cover_image FROM books LIMIT 5;
```

## Functional Testing

### Test 1: Book with Uploaded Cover

**Setup:**
1. Create a test book (or edit existing)
2. Upload a cover image (JPG/PNG)
3. Save book with cover

**Steps:**
1. Navigate to book details page
2. Check if cover image displays
3. Right-click → Inspect Element
4. Verify `src` contains `/uploads/covers/`

**Expected Result:**
- Cover image displays immediately ✓
- No fallback chain needed ✓

### Test 2: Book with ISBN but No Uploaded Cover

**Setup:**
1. Find/create book with ISBN
2. Remove/clear cover field
3. Ensure ISBN is valid (13 digits)

**Steps:**
1. Navigate to book details page
2. Open browser DevTools → Network
3. Check image requests

**Expected Result:**
```
One of these should load successfully:
- https://covers.openlibrary.org/b/isbn/{ISBN}-L.jpg
- Browser shows book cover
Time: 200-500ms
```

### Test 3: Book Without ISBN or Cover

**Setup:**
1. Find/create book with no ISBN
2. No uploaded cover

**Steps:**
1. Navigate to book details page
2. Check what displays

**Expected Result:**
- Emoji placeholder displays: 📖
- No broken image icon
- No console errors

### Test 4: Invalid ISBN Format

**Setup:**
1. Create book with invalid ISBN (e.g., "ABC-123")
2. No uploaded cover

**Steps:**
1. Navigate to book details page
2. Check DevTools

**Expected Result:**
- System skips ISBN fallback (detects invalid)
- Shows placeholder immediately
- No API calls made
- Clean fallback

### Test 5: OpenLibrary Fallback

**Setup:**
1. Temporarily break the OpenLibrary URL in JavaScript
2. Book with ISBN

**Steps:**
1. Navigate to book details
2. Check Network tab
3. Watch for fallback attempts

**Expected Result:**
- First OpenLibrary attempt fails
- System tries alternative URL
- Falls back to placeholder
- Process is smooth

## Page-Specific Testing

### Landing Page (/home)

**Test Features:**
1. Scroll to "Featured Books" section
2. Verify each featured book has:
   - [ ] Cover image displaying
   - [ ] No broken image icons
   - [ ] Proper alt text
   - [ ] Quick loading

**Test Books:**
- Featured book with upload
- Featured book with ISBN
- Mix of covered and uncovered books

```html
<!-- Expected HTML -->
<div class="book-cover">
  <img src="[COVER_URL]" 
       alt="Book Title" 
       data-isbn="[ISBN]"
       onerror="...">
</div>
```

### Browse Page (/catalog)

**Test Features:**
1. Open catalog page
2. Verify book grid loads
3. Check each book card:
   - [ ] Cover displays
   - [ ] Title shows
   - [ ] Author shows
   - [ ] All cards have images
4. Apply filters
   - [ ] Filtered results still show covers

**Test Books:**
- Books with covers
- Books without covers
- Books with only ISBN
- Books with nothing

### Search Page (/catalog/search)

**Test Features:**
1. Search for different books
2. Verify each result:
   - [ ] Has cover image
   - [ ] Shows title
   - [ ] Shows author
3. Search for partial ISBN
4. Verify results have covers

### Book Details Page (/book-details/{id})

**Test Features:**
1. Open several book detail pages
2. Check main cover:
   - [ ] Displays correctly
   - [ ] Responsive sizing
   - [ ] Proper alt text
3. Check "Similar Books" section:
   - [ ] All similar books have covers
   - [ ] Fallback works for similar books
4. Test with books:
   - [ ] Uploaded cover
   - [ ] ISBN-based cover
   - [ ] No cover (placeholder)

### Wishlist Page (/user/wishlist)

**Test Features:**
1. Add books to wishlist
2. Navigate to wishlist page
3. Verify wishlist displays:
   - [ ] Book covers showing (NEWLY FIXED!)
   - [ ] Wishlist button visible
   - [ ] Can remove from wishlist
4. Test with:
   - [ ] Books with covers
   - [ ] Books without covers
   - [ ] Mix of both

**Important:** This page was previously showing only emoji placeholders. Should now show actual covers when available.

### Orders Page (/user/orders)

**Test Features:**
1. Create test orders (if possible)
2. Navigate to orders page
3. Verify order cards:
   - [ ] Book cover displays
   - [ ] Order details visible
   - [ ] Status shows correctly
4. Test with different cover scenarios

### Checkout Page (/payment/checkout/{id})

**Test Features:**
1. Add book to cart and go to checkout
2. Verify order summary:
   - [ ] Book cover displays
   - [ ] Cover is properly sized (thumbnail)
   - [ ] No broken images
   - [ ] Quick loading

## Browser Compatibility Testing

### Desktop Browsers

**Chrome/Chromium**
- [ ] All images load
- [ ] Fallback chain works
- [ ] Console has no errors
- [ ] Performance good

**Firefox**
- [ ] All images load
- [ ] Fallback chain works
- [ ] Console has no errors

**Safari**
- [ ] All images load
- [ ] Fallback chain works
- [ ] Console has no errors

**Edge**
- [ ] All images load
- [ ] Fallback chain works
- [ ] Console has no errors

### Mobile Browsers

**iOS Safari**
- [ ] Images load
- [ ] Responsive layout
- [ ] Touch works

**Chrome Mobile**
- [ ] Images load
- [ ] Responsive layout
- [ ] Touch works

## Performance Testing

### Load Time Measurements

```javascript
// In browser console
performance.mark('book-images-start');
// Load page...
performance.mark('book-images-end');
performance.measure('book-images', 'book-images-start', 'book-images-end');
console.log(performance.getEntriesByName('book-images')[0]);
```

**Acceptable Times:**
- Local upload: < 100ms
- ISBN fallback: < 500ms
- API fallback: < 1000ms
- Placeholder: Instant

### Network Monitoring

Open DevTools Network tab and check:

```
✓ No stalled requests
✓ Images load in parallel
✓ No unexpected API calls
✓ Cache headers set correctly
✓ API calls batched, not repeated
```

### Memory Usage

Check DevTools Memory tab:

```
✓ No memory leaks
✓ Images properly cached
✓ BookCoverManager properly garbage collected
✓ No duplicate event listeners
```

## Error Scenario Testing

### Scenario 1: Network Offline

**Steps:**
1. Developer Tools → Network → Offline
2. Load book page
3. Observe behavior

**Expected:**
- Local covers display
- Placeholder shown for ISBN books
- No error messages
- Clean graceful degradation

### Scenario 2: OpenLibrary Unavailable

**Steps:**
1. Block OpenLibrary in hosts file:
   ```
   127.0.0.1 covers.openlibrary.org
   ```
2. Load book with ISBN
3. Observe fallback

**Expected:**
- System tries fallback sources
- Eventually shows placeholder
- No JavaScript errors
- Page still usable

### Scenario 3: Missing ISBN Field

**Steps:**
1. Database: SET isbn = NULL for test book
2. Load book details
3. Verify behavior

**Expected:**
- No ISBN fallback attempted
- Shows placeholder if no cover
- No errors
- System handles gracefully

### Scenario 4: Invalid File Permissions

**Steps:**
1. Make uploads directory read-only:
   ```bash
   chmod 444 /public/uploads/covers/
   ```
2. Load book with local cover
3. Observe fallback

**Expected:**
- Local file check fails
- Falls back to ISBN/API
- No permission denied errors
- Works correctly

### Scenario 5: Corrupted Database Field

**Steps:**
1. Set cover_image to invalid value:
   ```sql
   UPDATE books SET cover_image = 'NOT_AN_IMAGE.xyz';
   ```
2. Load book
3. Verify fallback works

**Expected:**
- Invalid image skipped
- Falls back to ISBN
- System doesn't break
- Handles gracefully

## API Testing

### OpenLibrary API

**Test ISBNs:**
```
9780134687568 - Clean Code (valid)
9780756405890 - The Name of the Wind (valid)
9780134685991 - Effective Java (valid)
1234567890 - Invalid ISBN (test)
```

**Manual Test:**
```bash
# Test OpenLibrary API directly
curl https://covers.openlibrary.org/b/isbn/9780134687568-L.jpg \
  -w "\nStatus: %{http_code}\n"

# Expected: HTTP 200 with JPEG image
```

### Google Books API (Optional Testing)

**Manual Test:**
```bash
# Test Google Books API
curl "https://www.googleapis.com/books/v1/volumes?q=isbn:9780134687568" \
  -w "\nStatus: %{http_code}\n"

# Expected: HTTP 200 with JSON containing image URLs
```

## Console Logging

### Expected Console Output

When BookCoverManager initializes:

```javascript
// BookCoverManager should initialize
// Look for in console:
// - No errors
// - BookCoverManager instance created
// - Event listeners attached
// - Image fallback handlers installed
```

**Check for errors:**
```javascript
// In browser console, should be empty or have info only:
// No 'Uncaught Error' messages
// No 'Failed to load resource' critical errors
// No 'CORS' errors
```

## Automated Testing Script (Optional)

```javascript
// Place in browser console to test all covers on page:

function testBookCovers() {
    const images = document.querySelectorAll('[data-isbn]');
    console.log(`Testing ${images.length} images with ISBN data...`);
    
    images.forEach((img, idx) => {
        const isbn = img.dataset.isbn;
        const src = img.src;
        const loaded = img.complete && img.naturalHeight > 0;
        console.log(`[${idx}] ISBN: ${isbn} | Src: ${src} | Loaded: ${loaded}`);
    });
}

testBookCovers();
```

## Success Criteria

### Minimum Requirements ✓

- [ ] No broken image icons appear anywhere
- [ ] Every book card displays either:
  - [ ] Uploaded cover image, OR
  - [ ] ISBN-based cover from API, OR
  - [ ] Clean emoji placeholder 📖
- [ ] All affected pages work:
  - [ ] Landing (featured books)
  - [ ] Browse/catalog
  - [ ] Search results
  - [ ] Book details (main + similar)
  - [ ] Wishlist (NEWLY FIXED)
  - [ ] Orders
  - [ ] Checkout
- [ ] No JavaScript errors in console
- [ ] All pages load in reasonable time

### Recommended Requirements ✓

- [ ] Mobile browsers tested
- [ ] Fast performance (<500ms for most covers)
- [ ] API failures handled gracefully
- [ ] Network offline handled gracefully
- [ ] Performance monit or shows no issues
- [ ] Memory profiler shows no leaks
- [ ] Accessibility standards met
- [ ] All alt text present and descriptive

## Rollback Plan

If issues occur:

1. **Disable JavaScript manager:**
   ```php
   // In /layouts/footer.php, comment out:
   // <script src="<?= asset('js/book-covers.js') ?>"></script>
   ```

2. **Revert to local covers only:**
   ```php
   // In getBookCover(), return null for all but local covers
   return null; // Skip ISBN-based fallbacks
   ```

3. **Database rollback:**
   ```sql
   -- If needed, revert to simpler queries
   SELECT cover_image FROM books WHERE id = ?;
   ```

## Post-Deployment Verification

After deployment, verify:

1. [ ] Books with covers display correctly
2. [ ] Books without covers show fallback
3. [ ] No error messages in logs
4. [ ] API usage is reasonable
5. [ ] Page performance is acceptable
6. [ ] Mobile display works
7. [ ] Admin can still manage books
8. [ ] No database issues

## Support Resources

- **Documentation:** `/BOOK_COVER_SYSTEM.md`
- **Visual Guide:** `/BOOK_COVER_VISUAL_GUIDE.md`
- **Logs:** `/storage/logs/`
- **Configuration:** `/config/config.php`

## Reporting Issues

If problems occur:

1. **Collect information:**
   - Browser/version
   - URL where issue occurs
   - Browser console errors
   - Network tab screenshots

2. **Check logs:**
   ```bash
   tail -f /storage/logs/app-*.log
   ```

3. **Test manually:**
   - Try different books
   - Check with/without ISBN
   - Test different browsers
   - Monitor network requests

4. **Report to developer:**
   - Include all logs
   - Screenshots of behavior
   - Steps to reproduce
   - Expected vs actual result

