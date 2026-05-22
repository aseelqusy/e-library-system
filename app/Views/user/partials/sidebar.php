<?php
$currentPage = $current_page ?? '';
?>
<aside class="dashboard-sidebar">
    <div class="sidebar-header">
        <h3>Menu</h3>
    </div>
    <nav class="sidebar-nav">
        <a href="<?= url('dashboard') ?>" class="sidebar-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-gauge"></i>
            <span>Overview</span>
        </a>
        <a href="<?= url('user/borrows') ?>" class="sidebar-item <?= $currentPage === 'borrows' ? 'active' : '' ?>">
            <i class="fas fa-book"></i>
            <span>My Borrows</span>
        </a>
        <a href="<?= url('user/orders') ?>" class="sidebar-item <?= $currentPage === 'orders' ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>My Orders</span>
        </a>
        <a href="<?= url('user/wishlist') ?>" class="sidebar-item <?= $currentPage === 'wishlist' ? 'active' : '' ?>">
            <i class="fas fa-heart"></i>
            <span>Wishlist</span>
        </a>
        <a href="<?= url('user/history') ?>" class="sidebar-item <?= $currentPage === 'history' ? 'active' : '' ?>">
            <i class="fas fa-history"></i>
            <span>Borrow History</span>
        </a>
        <a href="<?= url('user/profile') ?>" class="sidebar-item <?= $currentPage === 'profile' ? 'active' : '' ?>">
            <i class="fas fa-user-cog"></i>
            <span>Profile Settings</span>
        </a>
    </nav>
</aside>

