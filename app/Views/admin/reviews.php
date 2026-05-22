<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="admin-layout">
    <?php View::partial('admin/sidebar'); ?>

    <main class="admin-main">
        <div class="flex items-center justify-between mb-6" style="justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2>💬 Manage Reviews</h2>
                <p class="text-muted text-sm"><?= (int)($pagination['total'] ?? 0) ?> total reviews</p>
            </div>
        </div>

        <form method="GET" action="<?= url('admin/reviews') ?>" class="filter-bar glass-card mb-4 admin-reviews-filters">
            <div class="search-input-wrap search-input">
                <div class="input-group">
                    <span class="input-icon">🔍</span>
                    <input type="text" class="form-control" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Search by user, book, or review content">
                </div>
            </div>
            <select class="form-control" name="rating" style="max-width:180px;">
                <option value="">All Ratings</option>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?= $i ?>" <?= (string)($filters['rating'] ?? '') === (string)$i ? 'selected' : '' ?>><?= $i ?> stars</option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            <a href="<?= url('admin/reviews') ?>" class="btn btn-secondary btn-sm">Reset</a>
        </form>

        <div class="card glass-card">
            <div class="table-wrapper">
                <table>
                    <thead>
                    <tr>
                        <th>User</th>
                        <th>Book</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($reviews)): ?>
                        <tr>
                            <td colspan="6" class="text-muted text-center" style="padding:24px;">No reviews found for your current filters.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td><strong><?= e($review['user_name'] ?? 'Unknown') ?></strong></td>
                                <td><?= e($review['book_title'] ?? 'Unknown') ?></td>
                                <td><?= rating_stars((float)($review['rating'] ?? 0)) ?></td>
                                <td>
                                    <p class="admin-review-text"><?= e($review['comment'] ?? '') ?></p>
                                </td>
                                <td class="text-muted text-sm"><?= !empty($review['created_at']) ? formatDate($review['created_at']) : 'N/A' ?></td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm admin-review-delete"
                                        data-id="<?= (int)$review['id'] ?>"
                                        data-label="<?= e($review['book_title'] ?? 'this review') ?>"
                                    >Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (($pagination['pages'] ?? 1) > 1): ?>
            <div class="admin-pagination">
                <?php for ($p = 1; $p <= (int)$pagination['pages']; $p++): ?>
                    <?php
                    $query = ['page' => $p];
                    if (!empty($filters['q'])) {
                        $query['q'] = $filters['q'];
                    }
                    if (!empty($filters['rating'])) {
                        $query['rating'] = $filters['rating'];
                    }
                    ?>
                    <a
                        href="<?= url('admin/reviews?' . http_build_query($query)) ?>"
                        class="btn btn-sm <?= $p === (int)$pagination['page'] ? 'btn-primary' : 'btn-secondary' ?>"
                    ><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const base = document.querySelector('meta[name="base-url"]')?.content || '';
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

    document.querySelectorAll('.admin-review-delete').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const label = btn.dataset.label || 'this review';
            if (!window.confirm('Delete review for "' + label + '"?')) {
                return;
            }

            btn.disabled = true;
            const original = btn.textContent;
            btn.textContent = 'Deleting...';

            try {
                const response = await fetch(base + '/admin/reviews/delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id, _token: token }).toString()
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Delete failed.');
                }
                window.location.reload();
            } catch (error) {
                if (typeof App !== 'undefined' && App?.Toast?.show) {
                    App.Toast.show(error.message || 'Delete failed.', 'error');
                } else {
                    alert(error.message || 'Delete failed.');
                }
                btn.disabled = false;
                btn.textContent = original;
            }
        });
    });
});
</script>

<?php View::includeLayout('footer'); ?>

