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
