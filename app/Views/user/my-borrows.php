<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-wrapper" style="padding-top: var(--navbar-height);">
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="text-gradient">My Borrows</h2>
                <a href="<?= url('catalog') ?>" class="btn btn-primary btn-sm">+ Borrow New</a>
            </div>

            <?php if (empty($borrows)): ?>
                <div class="text-center" style="padding:60px 0;">
                    <p style="font-size:3rem;margin-bottom:12px;">📚</p>
                    <h3>No active borrows</h3>
                    <p class="text-muted">Browse our catalog to borrow your first book.</p>
                    <a href="<?= url('catalog') ?>" class="btn btn-primary mt-4">Browse Catalog</a>
                </div>
            <?php else: ?>
                <div class="card glass-card">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Author</th>
                                    <th>Borrow Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($borrows as $b): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= url('books/' . $b['book_id']) ?>" style="color:var(--text);font-weight:500;">
                                                <?= e($b['book']['title'] ?? 'Unknown') ?>
                                            </a>
                                        </td>
                                        <td class="text-muted"><?= e($b['book']['author'] ?? '') ?></td>
                                        <td><?= formatDate($b['borrow_date']) ?></td>
                                        <td><?= formatDate($b['due_date']) ?></td>
                                        <td><?= status_chip($b['status']) ?></td>
                                        <td>
                                            <?php if ($b['status'] === 'active'): ?>
                                                <button class="btn btn-sm btn-secondary"
                                                        onclick="fetch('<?= url('borrow/return') ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'borrow_id=<?= $b['id'] ?>&_token=<?= Csrf::token() ?>'}).then(r=>r.json()).then(d=>App.Toast.show(d.message,'success'))">
                                                    Return
                                                </button>
                                            <?php elseif ($b['status'] === 'overdue'): ?>
                                                <span class="text-xs" style="color:var(--danger);">Please return ASAP</span>
                                            <?php else: ?>
                                                <span class="text-muted text-xs">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php View::includeLayout('footer'); ?>
