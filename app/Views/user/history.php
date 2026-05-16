<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-wrapper" style="padding-top: var(--navbar-height);">
    <section class="section">
        <div class="container container-md">
            <div class="section-header">
                <h2 class="text-gradient">Borrow History</h2>
            </div>

            <?php if (empty($borrows)): ?>
                <div class="text-center" style="padding:60px 0;">
                    <p style="font-size:3rem;margin-bottom:12px;">📜</p>
                    <h3>No history yet</h3>
                    <p class="text-muted">Your borrowing timeline will appear here.</p>
                </div>
            <?php else: ?>
                <div class="timeline">
                    <?php foreach ($borrows as $b):
                        $cls = $b['status'] === 'returned' ? 'completed'
                             : ($b['status'] === 'overdue' ? 'overdue' : '');
                    ?>
                        <div class="timeline-item <?= $cls ?>">
                            <div class="timeline-date"><?= formatDate($b['borrow_date']) ?></div>
                            <div class="timeline-content glass-card p-4">
                                <div class="flex items-center justify-between" style="justify-content:space-between;flex-wrap:wrap;gap:8px;">
                                    <div>
                                        <strong>
                                            <a href="<?= url('books/' . $b['book_id']) ?>">
                                                <?= e($b['book']['title'] ?? 'Unknown') ?>
                                            </a>
                                        </strong>
                                        <span class="text-muted text-sm"> by <?= e($b['book']['author'] ?? '') ?></span>
                                    </div>
                                    <?= status_chip($b['status']) ?>
                                </div>
                                <div class="text-sm text-muted mt-2">
                                    Due: <?= formatDate($b['due_date']) ?>
                                    <?php if ($b['return_date']): ?>
                                        · Returned: <?= formatDate($b['return_date']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php View::includeLayout('footer'); ?>
