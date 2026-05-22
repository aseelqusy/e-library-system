<?php
$title = $title ?? 'Activity Bookings';
$bookings = is_array($bookings ?? null) ? $bookings : [];
$stats = is_array($stats ?? null) ? $stats : ['total_bookings' => 0, 'total_seats' => 0];
?>
<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="admin-layout">
    <?php View::partial('admin/sidebar'); ?>

    <main class="admin-main">
        <div class="flex items-center justify-between mb-6" style="justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2>🎟️ Activity Bookings</h2>
                <p class="text-muted text-sm">Booked seats and activity details gathered from the Join Us form</p>
            </div>
        </div>

        <div class="kpi-grid mb-6">
            <div class="kpi-card glass-card">
                <div class="kpi-icon purple">🎟️</div>
                <div>
                    <div class="kpi-value" data-count="<?= (int)($stats['total_bookings'] ?? 0) ?>"><?= (int)($stats['total_bookings'] ?? 0) ?></div>
                    <div class="kpi-label">Bookings</div>
                </div>
            </div>
            <div class="kpi-card glass-card">
                <div class="kpi-icon cyan">🪑</div>
                <div>
                    <div class="kpi-value" data-count="<?= (int)($stats['total_seats'] ?? 0) ?>"><?= (int)($stats['total_seats'] ?? 0) ?></div>
                    <div class="kpi-label">Seats Booked</div>
                </div>
            </div>
        </div>

        <div class="card glass-card">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Attendee</th>
                            <th>Contact</th>
                            <th>Seats</th>
                            <th>Notes</th>
                            <th>Booked At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr><td colspan="6" class="text-muted text-center" style="padding:24px;">No activity bookings yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($booking['activity_title'] ?? '') ?></strong><br>
                                        <span class="text-muted text-sm"><?= !empty($booking['activity_date']) ? e($booking['activity_date']) : 'No date set' ?></span>
                                    </td>
                                    <td>
                                        <strong><?= e($booking['name'] ?? '') ?></strong>
                                        <br><span class="text-muted text-sm">User #<?= (int)($booking['user_id'] ?? 0) ?: 'Guest' ?></span>
                                    </td>
                                    <td>
                                        <div class="text-sm"><?= e($booking['email'] ?? '') ?></div>
                                        <div class="text-muted text-sm"><?= e($booking['phone'] ?? '') ?></div>
                                    </td>
                                    <td><span class="chip chip-primary"><?= (int)($booking['seats'] ?? 0) ?></span></td>
                                    <td><p class="admin-review-text" style="max-width:360px;"><?= e($booking['message'] ?? '') ?></p></td>
                                    <td class="text-muted text-sm"><?= !empty($booking['created_at']) ? formatDate($booking['created_at']) : 'N/A' ?></td>
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

