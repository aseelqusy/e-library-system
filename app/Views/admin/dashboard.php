<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="admin-layout">
    <?php View::partial('admin/sidebar'); ?>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="flex items-center justify-between mb-6" style="justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <button class="btn btn-ghost btn-icon sidebar-toggle" aria-label="Toggle sidebar" style="display:none;">☰</button>
                <h2>Dashboard</h2>
                <p class="text-muted text-sm">Welcome back, <?= e(Auth::user()['name']) ?></p>
            </div>
            <div class="flex gap-2">
                <span class="text-sm text-muted"><?= date('l, F j, Y') ?></span>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card glass-card">
                <div class="kpi-icon purple">📚</div>
                <div>
                    <div class="kpi-value" data-count="<?= $totalBooks ?>"><?= $totalBooks ?></div>
                    <div class="kpi-label">Total Books</div>
                    <div class="kpi-trend">This month: <?= $booksThisMonth ?> · Last month: <?= $booksLastMonth ?></div>
                </div>
            </div>
            <div class="kpi-card glass-card">
                <div class="kpi-icon cyan">👥</div>
                <div>
                    <div class="kpi-value" data-count="<?= $totalUsers ?>"><?= $totalUsers ?></div>
                    <div class="kpi-label">Registered Users</div>
                    <div class="kpi-trend">This month: <?= $usersThisMonth ?> · Last month: <?= $usersLastMonth ?></div>
                </div>
            </div>
            <div class="kpi-card glass-card">
                <div class="kpi-icon amber">📖</div>
                <div>
                    <div class="kpi-value" data-count="<?= $activeBorrows ?>"><?= $activeBorrows ?></div>
                    <div class="kpi-label">Active Borrows</div>
                    <div class="kpi-trend">This month: <?= $borrowsThisMonth ?> · Last month: <?= $borrowsLastMonth ?></div>
                </div>
            </div>
            <div class="kpi-card glass-card">
                <div class="kpi-icon red">⚠</div>
                <div>
                    <div class="kpi-value" data-count="<?= $overdueCount ?>"><?= $overdueCount ?></div>
                    <div class="kpi-label">Overdue</div>
                    <div class="kpi-trend">Total borrows: <?= $totalBorrows ?></div>
                </div>
            </div>
        </div>

        <div class="kpi-grid" style="margin-top:16px;">
            <div class="kpi-card glass-card">
                <div class="kpi-icon purple">🏷️</div>
                <div>
                    <div class="kpi-value" data-count="<?= $totalCategories ?>"><?= $totalCategories ?></div>
                    <div class="kpi-label">Categories</div>
                </div>
            </div>
            <div class="kpi-card glass-card">
                <div class="kpi-icon cyan">⭐</div>
                <div>
                    <div class="kpi-value" data-count="<?= $totalReviews ?>"><?= $totalReviews ?></div>
                    <div class="kpi-label">Reviews</div>
                </div>
            </div>
            <div class="kpi-card glass-card">
                <div class="kpi-icon amber">❤️</div>
                <div>
                    <div class="kpi-value" data-count="<?= $totalWishlists ?>"><?= $totalWishlists ?></div>
                    <div class="kpi-label">Wishlists</div>
                </div>
            </div>
            <div class="kpi-card glass-card">
                <div class="kpi-icon red">📊</div>
                <div>
                    <div class="kpi-value" data-count="<?= $totalBorrows ?>"><?= $totalBorrows ?></div>
                    <div class="kpi-label">Total Borrows</div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="admin-grid">
            <div class="chart-container glass-card">
                <h3 class="mb-4">Borrowing Activity</h3>
                <canvas id="chart-borrows" height="260"></canvas>
            </div>
            <div class="chart-container glass-card">
                <h3 class="mb-4">Category Distribution</h3>
                <canvas id="chart-categories" height="260"></canvas>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card glass-card">
            <div class="card-header">
                <h3>Recent Activity</h3>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Event</th><th>User</th><th>Details</th><th>Time</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentBorrows)): ?>
                            <tr><td colspan="4" class="text-muted text-sm">No recent activity.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentBorrows as $row): ?>
                                <?php
                                    $label = match ($row['status']) {
                                        'returned' => 'Returned',
                                        'overdue'  => 'Overdue',
                                        'reserved' => 'Reserved',
                                        default    => 'Borrowed',
                                    };
                                    $detail = $label . ' "' . ($row['book_title'] ?? '') . '"';
                                    $time = $row['created_at'] ?? $row['borrow_date'] ?? null;
                                ?>
                                <tr>
                                    <td><?= status_chip($row['status']) ?></td>
                                    <td><?= e($row['user_name'] ?? 'Unknown') ?></td>
                                    <td><?= e($detail) ?></td>
                                    <td class="text-muted text-sm"><?= $time ? timeAgo($time) : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
    window.DashboardData = <?= json_encode([
        'borrows' => $borrowChart,
        'categories' => [
            'labels' => array_values(array_map(fn($c) => $c['name'], $categoryChart)),
            'data' => array_values(array_map(fn($c) => (int)$c['total'], $categoryChart)),
        ],
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    document.addEventListener('DOMContentLoaded', () => {
        // Show sidebar toggle on mobile
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        if (sidebarToggle && window.innerWidth <= 1024) {
            sidebarToggle.style.display = 'flex';
        }
        window.addEventListener('resize', () => {
            if (sidebarToggle) sidebarToggle.style.display = window.innerWidth <= 1024 ? 'flex' : 'none';
        });

        // Simple Bar Chart - Borrowing Activity
        const borrowsCanvas = document.getElementById('chart-borrows');
        if (borrowsCanvas) {
            const ctx = borrowsCanvas.getContext('2d');
            const data = window.DashboardData?.borrows?.data || [];
            const labels = window.DashboardData?.borrows?.labels || [];
            const maxVal = Math.max(1, ...data) * 1.2;
            const w = borrowsCanvas.width = borrowsCanvas.parentElement.clientWidth - 48;
            const h = borrowsCanvas.height = 220;
            const padding = { top: 20, right: 20, bottom: 30, left: 40 };
            const chartW = w - padding.left - padding.right;
            const chartH = h - padding.top - padding.bottom;
            const barWidth = chartW / Math.max(1, data.length) * 0.6;
            const gap = chartW / Math.max(1, data.length);

            ctx.fillStyle = '#94a3b8';
            ctx.font = '10px Inter, sans-serif';
            ctx.textAlign = 'center';

            // Grid lines
            for (let i = 0; i <= 4; i++) {
                const y = padding.top + (chartH / 4) * i;
                ctx.strokeStyle = 'rgba(255,255,255,0.04)';
                ctx.beginPath();
                ctx.moveTo(padding.left, y);
                ctx.lineTo(w - padding.right, y);
                ctx.stroke();
                ctx.fillText(Math.round(maxVal - (maxVal / 4) * i), padding.left - 20, y + 4);
            }

            data.forEach((val, i) => {
                const x = padding.left + gap * i + (gap - barWidth) / 2;
                const barH = (val / maxVal) * chartH;
                const y = padding.top + chartH - barH;

                const grad = ctx.createLinearGradient(x, y, x, padding.top + chartH);
                grad.addColorStop(0, '#7c3aed');
                grad.addColorStop(1, 'rgba(124,58,237,0.1)');
                ctx.fillStyle = grad;
                ctx.beginPath();
                ctx.roundRect(x, y, barWidth, barH, [4, 4, 0, 0]);
                ctx.fill();

                ctx.fillStyle = '#94a3b8';
                ctx.fillText(labels[i] || '', x + barWidth / 2, h - 10);
            });
        }

        // Simple Donut Chart - Categories
        const catCanvas = document.getElementById('chart-categories');
        if (catCanvas) {
            const ctx = catCanvas.getContext('2d');
            const w = catCanvas.width = catCanvas.parentElement.clientWidth - 48;
            const h = catCanvas.height = 220;
            const centerX = w / 2;
            const centerY = h / 2;
            const radius = Math.min(w, h) / 2 - 30;
            const innerRadius = radius * 0.55;

            const labels = window.DashboardData?.categories?.labels || [];
            const values = window.DashboardData?.categories?.data || [];
            const colors = ['#7c3aed','#06b6d4','#f59e0b','#10b981','#3b82f6','#ef4444','#8b5cf6','#22c55e'];

            const total = values.reduce((s, d) => s + d, 0) || 1;
            let startAngle = -Math.PI / 2;

            values.forEach((value, i) => {
                const sliceAngle = (value / total) * Math.PI * 2;
                ctx.beginPath();
                ctx.arc(centerX, centerY, radius, startAngle, startAngle + sliceAngle);
                ctx.arc(centerX, centerY, innerRadius, startAngle + sliceAngle, startAngle, true);
                ctx.closePath();
                ctx.fillStyle = colors[i % colors.length];
                ctx.fill();

                const midAngle = startAngle + sliceAngle / 2;
                const labelR = radius + 16;
                const lx = centerX + Math.cos(midAngle) * labelR;
                const ly = centerY + Math.sin(midAngle) * labelR;
                ctx.fillStyle = '#94a3b8';
                ctx.font = '10px Inter, sans-serif';
                ctx.textAlign = Math.cos(midAngle) > 0 ? 'left' : 'right';
                ctx.fillText(labels[i] || '', lx, ly + 4);

                startAngle += sliceAngle;
            });

            ctx.fillStyle = '#e2e8f0';
            ctx.font = 'bold 24px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(total, centerX, centerY + 4);
            ctx.fillStyle = '#94a3b8';
            ctx.font = '10px Inter, sans-serif';
            ctx.fillText('Books', centerX, centerY + 20);
        }
    });
    </script>

<?php View::includeLayout('footer'); ?>
