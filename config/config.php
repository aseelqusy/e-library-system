<?php

/* ───────────────────────────────────────────────
   Application Configuration
   ─────────────────────────────────────────────── */

define('APP_NAME',    'Luminara Library');
define('APP_VERSION', '1.0.0');
define('BASE_URL',    '/library-app/public');
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
define('APP_PATH',    ROOT_PATH . '/app');
define('CORE_PATH',   ROOT_PATH . '/core');
define('PUBLIC_PATH',  ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');

/* Database (for future MySQL integration) */
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'luminara_library');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/* Session */
define('SESSION_NAME',     'luminara_session');
define('SESSION_LIFETIME', 3600);

/* App settings */
define('ITEMS_PER_PAGE', 12);
define('ENABLE_MUSIC',   true);

/* Uploads */
define('UPLOAD_DIR', PUBLIC_PATH . '/uploads');
define('UPLOAD_MAX_IMAGE_BYTES', 5 * 1024 * 1024);
define('UPLOAD_MAX_PDF_BYTES', 25 * 1024 * 1024);

/* Admin access control */
$adminEmailEnv = getenv('ADMIN_EMAIL_WHITELIST');
$adminEmails = $adminEmailEnv ? array_map('trim', explode(',', $adminEmailEnv)) : [
    'admin@example.com',
    'soso@example.com',
    'admin@library.com',
];
define('ADMIN_EMAIL_WHITELIST', $adminEmails);
