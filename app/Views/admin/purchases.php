<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="admin-layout">
    <?php View::partial('admin/sidebar'); ?>

    <main class="admin-main">
        <div class="flex items-center justify-between mb-6" style="justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2>🛒 Purchases</h2>
                <p class="text-muted text-sm">Paid books and completed order records</p>
            </div>
        </div>

        <div class="kpi-grid mb-6">
            <div class="kpi-card glass-card">
                <div class="kpi-icon purple">🧾</div>
                <div>
                    <div class="kpi-value" data-count="<?= (int)($stats['paid_orders'] ?? 0) ?>"><?= (int)($stats['paid_orders'] ?? 0) ?></div>
                    <div class="kpi-label">Paid Purchases</div>
                </div>
            </div>
            <div class="kpi-card glass-card">
                <div class="kpi-icon amber">💰</div>
                <div>
                    <div class="kpi-value"><?= '$' . number_format((float)($stats['total_earnings'] ?? 0), 2) ?></div>
                    <div class="kpi-label">Total Earnings</div>
                </div>
            </div>
        </div>

        <div class="card glass-card">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Book</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Ordered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr><td colspan="7" class="text-muted text-center" style="padding:24px;">No paid purchases yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($order['user_name'] ?? 'Unknown') ?></strong><br>
                                        <span class="text-muted text-sm"><?= e($order['user_email'] ?? '') ?></span>
                                    </td>
                                    <td>
                                        <strong><?= e($order['book_title'] ?? 'Unknown') ?></strong><br>
                                        <span class="text-muted text-sm"><?= e($order['book_author'] ?? '') ?></span>
                                    </td>
                                    <td><?= (int)($order['quantity'] ?? 0) ?></td>
                                    <td>$<?= number_format((float)($order['unit_price'] ?? 0), 2) ?></td>
                                    <td>$<?= number_format((float)($order['total_price'] ?? 0), 2) ?></td>
                                    <td><?= status_chip((string)($order['status'] ?? 'paid')) ?></td>
                                    <td class="text-muted text-sm"><?= !empty($order['created_at']) ? formatDate($order['created_at']) : 'N/A' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php View::includeLayout('footer'); ?>

