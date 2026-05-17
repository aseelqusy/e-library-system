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
    ];
    $color = $colors[$status] ?? 'muted';
    return '<span class="chip chip-' . $color . '">' . ucfirst(e($status)) . '</span>';
}

function get_book_cover_cached(?string $coverImage, ?string $isbn): ?string {
    $coverImage = trim($coverImage ?? '');
    $isbn = trim($isbn ?? '');

    // 1. إذا كانت هناك صورة مرفوعة ومخزنة في قاعدة البيانات
    if (!empty($coverImage) && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $coverImage)) {
        if (strpos($coverImage, 'uploads/') !== false) {
            return url(ltrim($coverImage, '/'));
        }
        return url('uploads/images/' . ltrim($coverImage, '/'));
    }

    // 2. إذا اعتمدنا على الـ ISBN
    if (!empty($isbn)) {
        // تحويل الأرقام وتنظيفها
        $arabic_eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $arabic_western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $cleanIsbn = str_replace($arabic_eastern, $arabic_western, $isbn);
        $cleanIsbn = preg_replace('/[^0-9]/', '', $cleanIsbn);

        $fileName = "isbn-" . $cleanIsbn . ".jpg";

        $localFile = $_SERVER['DOCUMENT_ROOT'] . BASE_URL . '/uploads/covers/' . $fileName;
        if (!file_exists($localFile)) {
            $localFile = dirname(__DIR__) . '/public/uploads/covers/' . $fileName;
        }

        // إذا وُجد الغلاف محلياً نرجعه فوراً
        if (file_exists($localFile)) {
            return url("uploads/covers/" . $fileName);
        }}

        // بدلاً من الاستعانة بجوجل وظهور الصور الفارغة، نرجع null مباشرة
        // لتظهر الأيقونة 📖 من الـ PHP مباشرة بشكل نظيف وسريع
        return null;
    }
