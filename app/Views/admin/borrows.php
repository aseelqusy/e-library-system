<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="admin-layout">
    <?php View::partial('admin/sidebar'); ?>

    <main class="admin-main">
        <div class="flex items-center justify-between mb-6" style="justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2>📖 Manage Borrows</h2>
                <p class="text-muted text-sm"><?= count($borrows) ?> borrow records</p>
            </div>
        </div>

        <!-- Status filter -->
        <div class="tabs mb-4">
            <button class="tab active" data-status="">All</button>
            <button class="tab" data-status="active">Active</button>
            <button class="tab" data-status="overdue">Overdue</button>
            <button class="tab" data-status="returned">Returned</button>
            <button class="tab" data-status="reserved">Reserved</button>
        </div>

        <!-- Borrows Table -->
        <div class="card glass-card">
            <div class="table-wrapper">
                <table id="borrows-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Book</th>
                            <th>User</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($borrows as $i => $borrow): ?>
                        <?php
                            $book = $borrow['book'] ?? null;
                            $user = $borrow['user'] ?? null;
                        ?>
                        <tr data-status="<?= $borrow['status'] ?>">
                            <td class="text-muted"><?= $i + 1 ?></td>
                            <td>
                                <div>
                                    <strong><?= $book ? e($book['title']) : 'Unknown' ?></strong>
                                    <div class="text-muted text-sm"><?= $book ? e($book['author']) : '' ?></div>
                                </div>
                            </td>
                            <td><?= $user ? e($user['name']) : 'Unknown' ?></td>
                            <td class="text-muted text-sm"><?= formatDate($borrow['borrow_date']) ?></td>
                            <td class="text-muted text-sm"><?= formatDate($borrow['due_date']) ?></td>
                            <td><?= status_chip($borrow['status']) ?></td>
                            <td>
                                <div class="flex gap-1">
                                    <?php if ($borrow['status'] === 'active' || $borrow['status'] === 'overdue'): ?>
                                        <button class="btn btn-primary btn-sm" onclick="markReturned(<?= $borrow['id'] ?>)">Mark Returned</button>
                                    <?php elseif ($borrow['status'] === 'reserved'): ?>
                                        <button class="btn btn-primary btn-sm" onclick="approveReservation(<?= $borrow['id'] ?>)">Approve</button>
                                        <button class="btn btn-secondary btn-sm" onclick="rejectReservation(<?= $borrow['id'] ?>)">Reject</button>
                                    <?php else: ?>
                                        <span class="text-muted text-sm">—</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.tabs .tab');
    const rows = document.querySelectorAll('#borrows-table tbody tr');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const status = tab.dataset.status;
            rows.forEach(row => {
                row.style.display = (!status || row.dataset.status === status) ? '' : 'none';
            });
        });
    });
});

function markReturned(id) {
    if (!confirm('Mark this borrow as returned?')) return;
    submitBorrowAction('admin/borrows/return', id);
}
function approveReservation(id) {
    submitBorrowAction('admin/borrows/approve', id);
}
function rejectReservation(id) {
    submitBorrowAction('admin/borrows/reject', id);
}
function submitBorrowAction(path, id) {
    const base = document.querySelector('meta[name="base-url"]')?.content || '';
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

    fetch(base + '/' + path, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&_token=${encodeURIComponent(token)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            window.LuminApp?.Toast?.show(data.message || 'Action failed.', 'error');
        }
    })
    .catch(() => window.LuminApp?.Toast?.show('Action failed.', 'error'));
}
</script>

<?php View::includeLayout('footer'); ?>
