<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="admin-layout">
    <?php View::partial('admin/sidebar'); ?>

    <main class="admin-main">
        <div class="flex items-center justify-between mb-6" style="justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2>✉️ Contact Messages</h2>
                <p class="text-muted text-sm"><?= count($messages ?? []) ?> messages submitted from the Contact Us page</p>
            </div>
        </div>

        <div class="kpi-grid mb-6">
            <div class="kpi-card glass-card">
                <div class="kpi-icon purple">✉️</div>
                <div>
                    <div class="kpi-value" data-count="<?= count($messages ?? []) ?>"><?= count($messages ?? []) ?></div>
                    <div class="kpi-label">Total Messages</div>
                </div>
            </div>
        </div>

        <div class="card glass-card">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($messages)): ?>
                            <tr><td colspan="5" class="text-muted text-center" style="padding:24px;">No contact messages yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <tr>
                                    <td><strong><?= e($message['name'] ?? '') ?></strong></td>
                                    <td class="text-muted"><?= e($message['email'] ?? '') ?></td>
                                    <td><?= e($message['subject'] ?? '') ?></td>
                                    <td><p class="admin-review-text" style="max-width:520px;"><?= e($message['message'] ?? '') ?></p></td>
                                    <td class="text-muted text-sm"><?= !empty($message['created_at']) ? formatDate($message['created_at']) : 'N/A' ?></td>
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

