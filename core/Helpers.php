<?php

/* ───────────────────────────────────────────────
   Global helper functions
   ─────────────────────────────────────────────── */

function asset(string $path): string {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function isActive(string $path): string {
    $currentUrl = trim($_GET['url'] ?? '', '/');
    return ($currentUrl === trim($path, '/')) ? 'active' : '';
}

function formatDate(string $date): string {
    return date('M d, Y', strtotime($date));
}

function timeAgo(string $date): string {
    $diff = time() - strtotime($date);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return formatDate($date);
}

function truncate(string $text, int $length = 100): string {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

function setFlash(string $type, string $message): void {
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    $flash = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return $flash;
}

function rating_stars(float $rating): string {
    $html = '<span class="stars" aria-label="Rating: ' . $rating . ' out of 5">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($rating)) {
            $html .= '<i class="star filled">★</i>';
        } elseif ($i - $rating < 1 && $i - $rating > 0) {
            $html .= '<i class="star half">★</i>';
        } else {
            $html .= '<i class="star empty">☆</i>';
        }
    }
    $html .= '</span>';
    return $html;
}

function status_chip(string $status): string {
    $colors = [
        'active'    => 'success',
        'borrowed'  => 'info',
        'returned'  => 'muted',
        'overdue'   => 'danger',
        'reserved'  => 'warning',
        'available' => 'success',
        'pending'   => 'warning',
        'paid'      => 'success',
        'cancelled' => 'danger',
        'refunded'  => 'info',
    ];
    $color = $colors[$status] ?? 'muted';
    return '<span class="chip chip-' . $color . '">' . ucfirst(e($status)) . '</span>';
}

/**
 * Get book cover with fallback priority:
 * 1. Uploaded local cover image
 * 2. Local cached ISBN cover
 * 3. OpenLibrary ISBN cover
 * 4. Client-side fallback to placeholder
 *
 * Important: this function must stay fast because it runs during page render.
 * Any cache warming is done later in the browser via a background request.
 */
function getBookCover(?array $book): ?string {
    if (!is_array($book)) {
        return null;
    }

    $coverImage = trim($book['cover_image'] ?? $book['cover'] ?? '');
    $isbn = trim($book['isbn'] ?? '');

    // 1. Check for uploaded local cover image (highest priority)
    if (!empty($coverImage) && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $coverImage)) {
        $coverPath = null;
        if (strpos($coverImage, 'http') === 0) {
            // Already a full URL
            $coverPath = $coverImage;
        } elseif (strpos($coverImage, 'uploads/') === 0) {
            $coverPath = url($coverImage);
        } else {
            // Try to locate the file
            $coverPath = url('uploads/covers/' . ltrim($coverImage, '/'));
        }

        // Quick local file check (no remote checks - too slow)
        if ($coverPath && !strpos($coverPath, 'http') && @file_exists(str_replace(BASE_URL, PUBLIC_PATH, $coverPath))) {
            return $coverPath;
        }
    }

    // 2. Check for local cached ISBN cover (from previous API downloads)
    if (!empty($isbn)) {
        $cleanIsbn = clean_isbn($isbn);
        if ($cleanIsbn) {
            $cachedFile = get_local_isbn_cover($cleanIsbn);
            if ($cachedFile) {
                return $cachedFile; // Return cached file - INSTANT!
            }

            // 3. If not cached, return OpenLibrary URL directly.
            // Background cache warming is triggered after page load.
            return "https://covers.openlibrary.org/b/isbn/{$cleanIsbn}-L.jpg";
        }
    }

    // 4. Return null - client will show placeholder
    return null;
}

/**
 * Queue a cover image to be downloaded and cached locally.
 * This should only be called from background requests, never during page render.
 */
function queue_cover_cache(string $cleanIsbn): void {
    // Check if already cached (prevent re-queueing)
    if (get_local_isbn_cover($cleanIsbn)) {
        return;
    }

    // Cache synchronously from a background request
    cache_cover_from_openlibrary($cleanIsbn);
}

/**
 * Download and cache a cover from OpenLibrary
 * Saves to: /uploads/covers/isbn-{ISBN}.jpg
 */
function cache_cover_from_openlibrary(string $cleanIsbn): bool {
    $coverUrl = "https://covers.openlibrary.org/b/isbn/{$cleanIsbn}-L.jpg";
    $fileName = "isbn-" . $cleanIsbn . ".jpg";

    // Determine local path
    $localDir = PUBLIC_PATH . '/uploads/covers/';
    $localPath = $localDir . $fileName;

    // Ensure directory exists
    if (!@is_dir($localDir)) {
        @mkdir($localDir, 0755, true);
    }

    // Only cache if writable
    if (!@is_writable($localDir)) {
        return false;
    }

    // Skip if already cached
    if (@file_exists($localPath)) {
        return true;
    }

    try {
        // Download with timeout and size limit (2MB max)
        $streamContext = stream_context_create([
            'http' => ['timeout' => 5],
            'https' => ['timeout' => 5],
        ]);

        $coverData = @file_get_contents($coverUrl, false, $streamContext, 0, 2 * 1024 * 1024);

        if ($coverData === false || strlen($coverData) < 100) {
            // Download failed or file too small
            return false;
        }

        // Verify it's actually an image
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $coverData);
        finfo_close($finfo);

        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            // Not a valid image
            return false;
        }

        // Save to local cache
        $saved = @file_put_contents($localPath, $coverData, LOCK_EX);

        if ($saved) {
            // Log successful cache
            logDebug("Cover cached for ISBN {$cleanIsbn}", [
                'size' => strlen($coverData),
                'path' => $localPath,
            ], 'book-covers');
            return true;
        }
    } catch (Throwable $e) {
        error_log("Failed to cache cover for ISBN {$cleanIsbn}: " . $e->getMessage());
    }

    return false;
}

/**
 * Batch cache covers for multiple ISBNs.
 * Intended for background warming requests, not page rendering.
 */
function batch_cache_covers(array $isbns): array {
    $results = [
        'cached' => 0,
        'skipped' => 0,
        'failed' => 0,
    ];

    foreach ($isbns as $isbn) {
        $cleanIsbn = clean_isbn($isbn);
        if (!$cleanIsbn) {
            $results['failed']++;
            continue;
        }

        // Check if already cached
        if (get_local_isbn_cover($cleanIsbn)) {
            $results['skipped']++;
            continue;
        }

        // Try to cache
        if (cache_cover_from_openlibrary($cleanIsbn)) {
            $results['cached']++;
        } else {
            $results['failed']++;
        }

        // Small delay keeps the background warmer from overwhelming the server.
        usleep(100000); // 100ms
    }

    return $results;
}

/**
 * Queue multiple book covers for batch caching.
 * Extracts ISBNs from a book array.
 */
function queue_batch_cover_cache(array $books): void {
    $isbns = [];
    foreach ($books as $book) {
        $isbn = $book['isbn'] ?? null;
        if (!empty($isbn)) {
            $isbns[] = $isbn;
        }
    }

    if (empty($isbns)) {
        return;
    }

    // Cache in background (fast, non-blocking)
    batch_cache_covers($isbns);
}

/**
 * Legacy function - kept for backward compatibility
 */
function get_book_cover_cached(?string $coverImage, ?string $isbn): ?string {
    return getBookCover([
        'cover_image' => $coverImage,
        'isbn' => $isbn,
    ]);
}

/**
 * Clean and normalize ISBN
 */
function clean_isbn(?string $isbn): ?string {
    if (empty($isbn)) {
        return null;
    }

    // Convert Arabic numerals to Western
    $arabic_eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $arabic_western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $clean = str_replace($arabic_eastern, $arabic_western, $isbn);

    // Remove all non-numeric characters
    $clean = preg_replace('/[^0-9X]/', '', strtoupper($clean));

    // Return if valid ISBN-10 or ISBN-13
    if (strlen($clean) === 10 || strlen($clean) === 13) {
        return $clean;
    }

    return null;
}

/**
 * Check if local cached ISBN cover exists
 */
function get_local_isbn_cover(string $cleanIsbn): ?string {
    $fileName = "isbn-" . $cleanIsbn . ".jpg";
    $localPaths = [
        $_SERVER['DOCUMENT_ROOT'] . BASE_URL . '/uploads/covers/' . $fileName,
        dirname(__DIR__) . '/public/uploads/covers/' . $fileName,
        PUBLIC_PATH . '/uploads/covers/' . $fileName,
    ];

    foreach ($localPaths as $path) {
        if (@file_exists($path)) {
            return url("uploads/covers/{$fileName}");
        }
    }

    return null;
}

/**
 * Get cover image from Google Books API
 */
function get_google_books_cover(string $cleanIsbn): ?string {
    $cacheKey = "google_book_cover_{$cleanIsbn}";

    // Check session cache first (brief cache during request)
    if (isset($_SESSION[$cacheKey])) {
        $result = $_SESSION[$cacheKey];
        return $result ?: null;
    }

    $apiUrl = "https://www.googleapis.com/books/v1/volumes?q=isbn:{$cleanIsbn}";

    try {
        $response = @file_get_contents($apiUrl, false, null, 0, 5000); // 5KB limit
        if ($response === false) {
            $_SESSION[$cacheKey] = '';
            return null;
        }

        $data = json_decode($response, true);
        if (isset($data['items'][0]['volumeInfo']['imageLinks']['thumbnail'])) {
            $coverUrl = $data['items'][0]['volumeInfo']['imageLinks']['thumbnail'];
            // Convert HTTP to HTTPS
            $coverUrl = str_replace('http://', 'https://', $coverUrl);
            $_SESSION[$cacheKey] = $coverUrl;
            return $coverUrl;
        }
    } catch (Throwable $e) {
        error_log("Google Books API error: " . $e->getMessage());
    }

    $_SESSION[$cacheKey] = '';
    return null;
}

/**
 * Check if a URL is accessible (with timeout)
 */
function is_url_accessible(string $url, int $timeout = 3): bool {
    $streamContext = stream_context_create([
        'http' => ['timeout' => $timeout],
        'https' => ['timeout' => $timeout],
    ]);

    $headers = @get_headers($url, 1, $streamContext);
    if ($headers === false) {
        return false;
    }

    $status = is_array($headers) ? explode(' ', $headers[0])[1] ?? 0 : 0;
    return in_array((int)$status, [200, 301, 302, 304], true);
}

/**
 * Check if a file exists locally or remotely
 */
function file_exists_remote_or_local(string $path): bool {
    // Check if it's a remote URL
    if (strpos($path, 'http') === 0) {
        return is_url_accessible($path);
    }

    // Check locally
    if (strpos($path, BASE_URL) !== false) {
        $localPath = str_replace(BASE_URL, PUBLIC_PATH, $path);
        return @file_exists($localPath);
    }

    return @file_exists($path);
}

/**
 * Get default book cover placeholder SVG
 */
function get_default_book_cover_svg(): string {
    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 450" preserveAspectRatio="xMidYMid meet"><defs><linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#667eea;stop-opacity:1" /><stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" /></linearGradient></defs><rect width="300" height="450" fill="url(#grad)"/><rect x="20" y="30" width="260" height="390" rx="8" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2"/><circle cx="150" cy="140" r="50" fill="rgba(255,255,255,0.15)"/><text x="150" y="250" font-size="64" text-anchor="middle" fill="rgba(255,255,255,0.4)" font-family="Arial, sans-serif">📖</text><text x="150" y="350" font-size="16" text-anchor="middle" fill="rgba(255,255,255,0.6)" font-family="Arial, sans-serif">Book Cover</text></svg>');
}

function logDebug(string $message, ?array $context = null, string $channel = 'app'): void {
    $logDir = STORAGE_PATH . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $date = date('Y-m-d');
    $logFile = $logDir . '/' . $channel . '-' . $date . '.log';
    $timestamp = date('Y-m-d H:i:s.u');

    $contextStr = '';
    if ($context !== null) {
        // Safely encode context data
        $contextStr = ' | ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    $line = "[$timestamp] $message$contextStr" . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

function logError(string $message, ?Throwable $exception = null, ?array $context = null, string $channel = 'app'): void {
    $logDir = STORAGE_PATH . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $date = date('Y-m-d');
    $logFile = $logDir . '/' . $channel . '-' . $date . '.log';
    $timestamp = date('Y-m-d H:i:s.u');

    $contextStr = '';
    if ($context !== null) {
        $contextStr = ' | Context: ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    $exceptionStr = '';
    if ($exception !== null) {
        $exceptionStr = PHP_EOL . "  Exception: " . get_class($exception) . PHP_EOL .
                       "  Message: " . $exception->getMessage() . PHP_EOL .
                       "  Code: " . $exception->getCode() . PHP_EOL .
                       "  File: " . $exception->getFile() . ':' . $exception->getLine() . PHP_EOL .
                       "  Trace: " . str_replace(PHP_EOL, PHP_EOL . "    ", $exception->getTraceAsString());
    }

    $line = "[$timestamp] ERROR: $message$contextStr$exceptionStr" . PHP_EOL . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}
