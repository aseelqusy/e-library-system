<!-- Admin Sidebar Partial -->
<aside class="admin-sidebar" aria-label="Admin navigation">
    <div class="sidebar-section">
        <div class="sidebar-label">Overview</div>
        <a href="<?= url('admin/dashboard') ?>" class="sidebar-link <?= isActive('admin') ?: isActive('admin/dashboard') ?>">
            <span class="link-icon">📊</span> Dashboard
        </a>
    </div>
    <div class="sidebar-section">
        <div class="sidebar-label">Management</div>
        <a href="<?= url('admin/books') ?>" class="sidebar-link <?= isActive('admin/books') ?>">
            <span class="link-icon">📚</span> Books
        </a>
        <a href="<?= url('admin/users') ?>" class="sidebar-link <?= isActive('admin/users') ?>">
            <span class="link-icon">👥</span> Users
        </a>
        <a href="<?= url('admin/borrows') ?>" class="sidebar-link <?= isActive('admin/borrows') ?>">
            <span class="link-icon">📖</span> Borrows
        </a>
        <a href="<?= url('admin/categories') ?>" class="sidebar-link <?= isActive('admin/categories') ?>">
            <span class="link-icon">🏷️</span> Categories
        </a>
        <a href="<?= url('admin/reviews') ?>" class="sidebar-link <?= isActive('admin/reviews') ?>">
            <span class="link-icon">💬</span> Reviews
        </a>
        <a href="<?= url('admin/messages') ?>" class="sidebar-link <?= isActive('admin/messages') ?>">
            <span class="link-icon">✉️</span> Messages
        </a>
        <a href="<?= url('admin/purchases') ?>" class="sidebar-link <?= isActive('admin/purchases') ?>">
            <span class="link-icon">🛒</span> Purchases
        </a>
        <a href="<?= url('admin/activities') ?>" class="sidebar-link <?= isActive('admin/activities') ?>">
            <span class="link-icon">🎟️</span> Activities
        </a>
    </div>
    <div class="sidebar-section">
        <div class="sidebar-label">Analytics</div>
        <a href="<?= url('admin/reports') ?>" class="sidebar-link <?= isActive('admin/reports') ?>">
            <span class="link-icon">📈</span> Reports
        </a>
    </div>
    <div class="sidebar-section">
        <div class="sidebar-label">System</div>
        <a href="<?= url('admin/settings') ?>" class="sidebar-link <?= isActive('admin/settings') ?>">
            <span class="link-icon">⚙️</span> Settings
        </a>
    </div>
</aside>
