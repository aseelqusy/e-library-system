<?php
/**
 * Luminara Library — Single Entry Point
 * All requests are routed through this file.
 */

session_name('luminara_session');
session_start();

/* Base paths --------------------------------------------------- */
define('ROOT_PATH', dirname(__DIR__));

/* Load configuration & core ------------------------------------ */
require_once ROOT_PATH . '/config/config.php';
require_once CORE_PATH . '/Helpers.php';
require_once CORE_PATH . '/Csrf.php';
require_once ROOT_PATH . '/core/Database.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/Validator.php';
require_once CORE_PATH . '/View.php';
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Router.php';

/* Bootstrap ---------------------------------------------------- */
Csrf::init();
Auth::init();

/* Define Routes ------------------------------------------------ */
$router = new Router();

// Home
$router->get('',     'HomeController@index');
$router->get('home', 'HomeController@index');

// Auth
$router->get('login',            'AuthController@loginForm');
$router->post('login',           'AuthController@login');
$router->get('register',         'AuthController@registerForm');
$router->post('register',        'AuthController@register');
$router->get('forgot-password',  'AuthController@forgotForm');
$router->post('forgot-password', 'AuthController@forgot');
$router->get('logout',           'AuthController@logout');

// Catalog
$router->get('catalog',              'CatalogController@browse');
$router->get('catalog/browse',       'CatalogController@browse');
$router->get('catalog/book/{id}',    'CatalogController@bookDetails');
$router->get('books/{id}',           'CatalogController@bookDetails');
$router->get('book-details/{id}',    'CatalogController@bookDetails');
$router->get('catalog/categories',   'CatalogController@categories');
$router->get('catalog/search',       'CatalogController@search');

// User area
$router->get('dashboard',             'UserController@dashboard');
$router->get('user/profile',          'UserController@profile');
$router->post('user/profile',         'UserController@updateProfile');
$router->get('user/borrows',          'UserController@borrows');
$router->get('user/wishlist',         'UserController@wishlist');
$router->post('user/wishlist/toggle', 'UserController@toggleWishlist');
$router->get('user/history',          'UserController@history');

// Borrow
$router->post('borrow/request', 'BorrowController@request');
$router->post('borrow/return',  'BorrowController@returnBook');
$router->post('borrow/reserve', 'BorrowController@reserve');

// Admin — Dashboard
$router->get('admin',              'AdminController@dashboard');
$router->get('admin-dashboard',    'AdminController@dashboard');
$router->get('admin/dashboard',    'AdminController@dashboard');

// Admin — Books
$router->get('admin/books',             'AdminController@books');
$router->post('admin/books/store',      'AdminController@storeBook');
$router->post('admin/books/update',     'AdminController@updateBook');
$router->post('admin/books/delete',     'AdminController@deleteBook');

// Admin — Users
$router->get('admin/users',              'AdminController@users');
$router->post('admin/users/role',        'AdminController@updateUserRole');
$router->post('admin/users/deactivate',  'AdminController@deactivateUser');
$router->post('admin/users/activate',    'AdminController@activateUser');   // ← NEW
$router->post('admin/users/delete',      'AdminController@deleteUser');
$router->post('user/update-password', 'UserController@updatePassword');
$router->post('user/delete-account', 'UserController@deleteAccount');

// Admin — Borrows
$router->get('admin/borrows',              'AdminController@borrows');
$router->post('admin/borrows/return',      'AdminController@markBorrowReturned');
$router->post('admin/borrows/approve',     'BorrowController@approve');      // تم التعديل هنا
$router->post('admin/borrows/reject',      'BorrowController@reject');       // تم التعديل هنا
$router->post('admin/borrows/adminReturn', 'BorrowController::adminReturn'); // السطر المساعد المضاف سابقاً

// Admin — Categories
$router->get('admin/categories',              'AdminController@categories');
$router->post('admin/categories/store',       'AdminController@storeCategory');
$router->post('admin/categories/update',      'AdminController@updateCategory');
$router->post('admin/categories/delete',      'AdminController@deleteCategory');
// Admin — Reports & Settings
// Admin — Reports & Settings
$router->get('admin/reports/export',       'ReportController@export'); // مضاف في الأعلى وموجه للـ ReportController
$router->get('admin/reports',   'AdminController@reports');
$router->get('admin/settings',  'AdminController@settings');
$router->post('admin/settings', 'AdminController@updateSettings');
// API
$router->get('api/books',      'ApiController@books');
$router->get('api/books/{id}', 'ApiController@book');
$router->get('api/categories', 'ApiController@categories');
$router->get('api/featured',   'ApiController@featured');
$router->get('api/search',     'ApiController@search');
$router->post('api/review',    'ApiController@addReview');
// Notifications
// Endpoint to mark notifications as read (existing)
$router->post('api/notifications/mark-read', 'ApiController@markNotificationsRead');
// Lightweight endpoint to clear notifications from the navbar UI
$router->post('api/notifications/clear', 'ApiController@clearNotifications');

/* Dispatch ----------------------------------------------------- */
$router->dispatch();
