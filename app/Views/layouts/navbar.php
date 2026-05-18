<?php
$user = Auth::user();
$isAdmin = Auth::isAdminAuthorized();
$notifCount = 0;
if ($user) {
    require_once APP_PATH . '/Models/Notification.php';
    $notifCount = Notification::unreadCount($user['id']);
    $notifications = Notification::forUser($user['id']);
}
?>
<nav class="navbar" role="navigation" aria-label="Main navigation">
    <div class="container">
        <a href="<?= url('') ?>" class="navbar-brand">
            <span class="brand-icon" aria-hidden="true">
                <img src="<?= url('uploads/images/logo.png') ?>" alt="<?= e(APP_NAME) ?> logo">
            </span>
            <span><?= APP_NAME ?></span>
        </a>

        <div class="navbar-nav" role="menubar">
            <a href="<?= url('') ?>" class="nav-link <?= isActive('') ?>" role="menuitem">Home</a>
            <a href="<?= url('catalog') ?>" class="nav-link <?= isActive('catalog') ?: isActive('catalog/browse') ?>" role="menuitem">Catalog</a>
            <a href="<?= url('catalog/categories') ?>" class="nav-link <?= isActive('catalog/categories') ?>" role="menuitem">Categories</a>
            <?php if ($user): ?>
                <a href="<?= url('dashboard') ?>" class="nav-link <?= isActive('dashboard') ?>" role="menuitem">Dashboard</a>

                <?php if ($isAdmin): ?>
                    <a href="<?= url('admin-dashboard') ?>" class="nav-link <?= isActive('admin') ?: isActive('admin/dashboard') ?: isActive('admin-dashboard') ?>" role="menuitem">Admin</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="navbar-actions">

            <button class="navbar-search-btn" onclick="App.CommandPalette.open()" aria-label="Open search">
                🔍 <span>Search...</span> <kbd>Ctrl+K</kbd>
            </button>
            <button id="theme-toggle" class="btn btn-ghost btn-icon" aria-label="Toggle theme"><span id="theme-icon">☀️</span></button>

            <?php if ($user): ?>
                <div class="relative">

                    <button class="notification-btn" aria-label="Notifications" title="Notifications">
                        🔔
                        <?php if ($notifCount > 0): ?>
                            <span class="notification-badge"><?= $notifCount ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="notification-dropdown" role="menu">
                        <div class="notification-dropdown-header">
                            <span>Notifications</span>
                            <?php if ($notifCount > 0): ?>
                                <span class="chip chip-primary"><?= $notifCount ?> new</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($notifications)):
                            // Sort by date desc
                            usort($notifications, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));
                            foreach (array_slice($notifications, 0, 5) as $n): ?>
                                <div class="notification-item <?= $n['is_read'] ? '' : 'unread' ?>">
                                    <div class="notif-message"><?= e($n['message']) ?></div>
                                    <div class="notif-time"><?= timeAgo($n['created_at']) ?></div>
                                </div>
                            <?php endforeach; endif; ?>
                    </div>
                </div>

                <div class="relative">
                    <a href="<?= url('user/profile') ?>" class="user-menu-btn" title="Profile">
                        <span class="user-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></span>
                        <span class="user-name"><?= e($user['name']) ?></span>
                    </a>
                </div>

                <a href="<?= url('logout') ?>" class="btn btn-ghost btn-sm" title="Sign out">↗ Logout</a>
            <?php else: ?>
                <a href="<?= url('login') ?>" class="btn btn-secondary btn-sm">Sign In</a>
                <a href="<?= url('register') ?>" class="btn btn-primary btn-sm">Register</a>
            <?php endif; ?>

            <button class="menu-toggle" aria-label="Toggle menu">☰</button>
        </div>
    </div>
</nav>