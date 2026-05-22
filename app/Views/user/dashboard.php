<?php View::includeLayout('header', ['title' => $title ?? 'My Dashboard']); ?>
<?php View::includeLayout('navbar'); ?>

    <div class="page-background"></div>
    <div class="page-wrapper user-dashboard-page" style="padding-top: var(--navbar-height);">
        <!-- Floating decorations -->
        <div class="floating-decorations" aria-hidden="true"></div>
        <section class="section dashboard-section">
            <div class="container">
                <div class="section-header dashboard-page-header">
                    <div>
                        <h2 class="text-gradient">My Dashboard</h2>
                        <p class="text-muted text-sm">Everything about your reading journey in one place.</p>
                    </div>
                    <a href="<?= url('catalog') ?>" class="btn btn-primary btn-sm">Browse Catalog</a>
                </div>

                <div class="dashboard-container">
            <!-- Left Sidebar Navigation -->
            <aside class="dashboard-sidebar">
                <div class="sidebar-header">
                    <h3>Menu</h3>
                </div>
                <nav class="sidebar-nav">
                    <a href="<?= url('dashboard') ?>" class="sidebar-item <?= (isset($current_page) && $current_page === 'dashboard') ? 'active' : '' ?>">
                        <i class="fas fa-gauge"></i>
                        <span>Overview</span>
                    </a>
                    <a href="<?= url('user/borrows') ?>" class="sidebar-item <?= (isset($current_page) && $current_page === 'borrows') ? 'active' : '' ?>">
                        <i class="fas fa-book"></i>
                        <span>My Borrows</span>
                    </a>
                    <a href="<?= url('user/orders') ?>" class="sidebar-item <?= (isset($current_page) && $current_page === 'orders') ? 'active' : '' ?>">
                        <i class="fas fa-shopping-cart"></i>
                        <span>My Orders</span>
                    </a>
                    <a href="<?= url('user/wishlist') ?>" class="sidebar-item <?= (isset($current_page) && $current_page === 'wishlist') ? 'active' : '' ?>">
                        <i class="fas fa-heart"></i>
                        <span>Wishlist</span>
                    </a>
                    <a href="<?= url('user/history') ?>" class="sidebar-item <?= (isset($current_page) && $current_page === 'history') ? 'active' : '' ?>">
                        <i class="fas fa-history"></i>
                        <span>Borrow History</span>
                    </a>
                    <a href="<?= url('user/profile') ?>" class="sidebar-item <?= (isset($current_page) && $current_page === 'profile') ? 'active' : '' ?>">
                        <i class="fas fa-user-cog"></i>
                        <span>Profile Settings</span>
                    </a>
                </nav>
            </aside>

            <!-- Main Content Area -->
            <main class="dashboard-main">
                <!-- Welcome Section -->
                <section class="welcome-section">
                    <div class="welcome-card glass-card">
                        <div class="welcome-content">
                            <div class="welcome-text">
                                <h2><?= !empty($isNewUser) ? 'Welcome, ' : 'Welcome back, ' ?><?= e(Auth::user()['name'] ?? 'Reader') ?></h2>
                                <p class="text-muted">Quick access to your library activity</p>
                                <div class="user-status">
                                    <span class="status-badge active">Active Member</span>
                                    <span class="member-since">Member since <?= !empty($member_since) ? date('F Y', strtotime($member_since)) : 'recently' ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Statistics Grid -->
                <section class="stats-section">
                    <h3 class="section-title">Your Activity</h3>
                    <div class="stats-grid">
                        <!-- Total Books Borrowed -->
                        <div class="stat-card glass-card">
                            <div class="stat-icon">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">Total Borrowed</p>
                                <p class="stat-value"><?= !empty($total_borrowed) ? $total_borrowed : '0' ?></p>
                            </div>
                            <div class="stat-trend up">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                        </div>

                        <!-- Active Loans -->
                        <div class="stat-card glass-card">
                            <div class="stat-icon">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">Active Loans</p>
                                <p class="stat-value"><?= !empty($active_loans) ? $active_loans : '0' ?></p>
                            </div>
                            <div class="stat-trend <?= (!empty($active_loans) && $active_loans > 0) ? 'warning' : 'up' ?>">
                                <i class="fas fa-arrow-<?= (!empty($active_loans) && $active_loans > 0) ? 'up' : 'down' ?>"></i>
                            </div>
                        </div>

                        <!-- Wishlist Items -->
                        <div class="stat-card glass-card">
                            <div class="stat-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">Wishlist Items</p>
                                <p class="stat-value"><?= !empty($wishlist_count) ? $wishlist_count : '0' ?></p>
                            </div>
                            <div class="stat-trend up">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                        </div>

                        <!-- Pending Orders -->
                        <div class="stat-card glass-card">
                            <div class="stat-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="stat-content">
                                <p class="stat-label">Pending Orders</p>
                                <p class="stat-value"><?= !empty($pending_orders) ? $pending_orders : '0' ?></p>
                            </div>
                            <div class="stat-trend <?= (!empty($pending_orders) && $pending_orders > 0) ? 'warning' : 'up' ?>">
                                <i class="fas fa-arrow-<?= (!empty($pending_orders) && $pending_orders > 0) ? 'up' : 'down' ?>"></i>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Recent Activity Section -->
                <section class="activity-section">
                    <div class="activity-header">
                        <h3 class="section-title">Recent Activity</h3>
                        <a href="<?= url('user/history') ?>" class="view-all-link">View All</a>
                    </div>

                    <div class="activity-timeline glass-card">
                        <?php if (!empty($recent_activity) && is_array($recent_activity)): ?>
                            <?php foreach (array_slice($recent_activity, 0, 5) as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon <?= $activity['type'] ?? 'default' ?>">
                                        <i class="fas fa-<?= $activity['icon'] ?? 'circle' ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p class="activity-action"><?= e($activity['action'] ?? 'Unknown action') ?></p>
                                        <p class="activity-time text-muted text-sm"><?= $activity['time'] ?? 'Recently' ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-activity">
                                <i class="fas fa-inbox"></i>
                                <p>No recent activity</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Quick Actions -->
                <section class="quick-actions-section">
                    <h3 class="section-title">Quick Actions</h3>
                    <div class="quick-actions-grid">
                        <a href="<?= url('catalog') ?>" class="action-card glass-card">
                            <div class="action-icon primary">
                                <i class="fas fa-search"></i>
                            </div>
                            <h4>Browse Catalog</h4>
                            <p class="text-sm text-muted">Discover new books</p>
                        </a>
                        <a href="<?= url('user/orders/new') ?>" class="action-card glass-card">
                            <div class="action-icon secondary">
                                <i class="fas fa-plus"></i>
                            </div>
                            <h4>Place Order</h4>
                            <p class="text-sm text-muted">Order new books</p>
                        </a>
                        <a href="<?= url('user/profile') ?>" class="action-card glass-card">
                            <div class="action-icon accent">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                            <h4>Settings</h4>
                            <p class="text-sm text-muted">Manage your account</p>
                        </a>
                    </div>
                </section>
            </main>
                </div>
            </div>
        </section>
    </div>

<?php View::includeLayout('footer'); ?>