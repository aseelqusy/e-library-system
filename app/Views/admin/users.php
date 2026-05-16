<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="admin-layout">
    <?php View::partial('admin/sidebar'); ?>

    <main class="admin-main">
        <div class="flex items-center justify-between mb-6" style="justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2>👥 Manage Users</h2>
                <p class="text-muted text-sm"><?= count($users) ?> registered users</p>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card glass-card">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $i => $user): ?>
                        <tr>
                            <td class="text-muted"><?= $i + 1 ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div class="avatar avatar-sm" style="background:var(--glass-bg);border:1px solid var(--glass-border);display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;font-weight:600;font-size:14px;">
                                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                    </div>
                                    <strong><?= e($user['name']) ?></strong>
                                </div>
                            </td>
                            <td class="text-muted"><?= e($user['email']) ?></td>
                            <td>
                                <span class="chip <?= $user['role'] === 'admin' ? 'chip-primary' : 'chip-outline' ?>">
                                    <?= $user['role'] === 'admin' ? '🛡 Admin' : '📖 Member' ?>
                                </span>
                            </td>
                            <td class="text-muted text-sm"><?= isset($user['created_at']) ? formatDate($user['created_at']) : 'N/A' ?></td>
                            <td>
                                <div class="flex gap-1">
                                    <button class="btn btn-ghost btn-sm" title="View profile" onclick="window.LuminApp?.Toast?.show('User profile view coming soon','info')">👁</button>
                                    <button class="btn btn-ghost btn-sm" title="Toggle role" onclick="toggleRole(<?= $user['id'] ?>, '<?= e($user['name']) ?>', '<?= $user['role'] ?>')">
                                        <?= $user['role'] === 'admin' ? '⬇' : '⬆' ?>
                                    </button>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                    <button class="btn btn-ghost btn-sm btn-danger-text" title="Deactivate" onclick="deactivateUser(<?= $user['id'] ?>, '<?= e($user['name']) ?>')">🚫</button>
                                    <button class="btn btn-ghost btn-sm btn-danger-text" title="Delete" onclick="deleteUser(<?= $user['id'] ?>, '<?= e($user['name']) ?>')">🗑</button>
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
function toggleRole(id, name, currentRole) {
    const newRole = currentRole === 'admin' ? 'member' : 'admin';
    if (!confirm('Change ' + name + ' role to ' + newRole + '?')) return;

    const base = document.querySelector('meta[name="base-url"]')?.content || '';
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

    fetch(base + '/admin/users/role', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&role=${newRole}&_token=${encodeURIComponent(token)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            window.LuminApp?.Toast?.show(data.message || 'Role update failed.', 'error');
        }
    })
    .catch(() => window.LuminApp?.Toast?.show('Role update failed.', 'error'));
}
function deactivateUser(id, name) {
    if (!confirm('Deactivate user "' + name + '"?')) return;

    const base = document.querySelector('meta[name="base-url"]')?.content || '';
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

    fetch(base + '/admin/users/deactivate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&_token=${encodeURIComponent(token)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            window.LuminApp?.Toast?.show(data.message || 'Deactivate failed.', 'error');
        }
    })
    .catch(() => window.LuminApp?.Toast?.show('Deactivate failed.', 'error'));
}
function deleteUser(id, name) {
    if (!confirm('Delete user "' + name + '"? This will remove all related records.')) return;

    const base = document.querySelector('meta[name="base-url"]')?.content || '';
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

    fetch(base + '/admin/users/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&_token=${encodeURIComponent(token)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            window.LuminApp?.Toast?.show(data.message || 'Delete failed.', 'error');
        }
    })
    .catch(() => window.LuminApp?.Toast?.show('Delete failed.', 'error'));
}
</script>

<?php View::includeLayout('footer'); ?>
