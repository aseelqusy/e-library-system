<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

    <div class="page-background"></div>
    <div class="admin-layout">
        <?php View::partial('admin/sidebar'); ?>

        <main class="admin-main">
            <div class="flex items-center justify-between mb-6" style="justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div>
                    <h2>👥 Manage Users</h2>
                    <p class="text-muted text-sm"><?= count($users) ?> registered users</p>
                </div>
            </div>

            <div class="card glass-card">
                <div class="table-wrapper">
                    <table>
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $i => $user): ?>
                            <?php $isActive = isset($user['is_active']) ? (bool)$user['is_active'] : true; ?>
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
                                <td>
                                    <?php if ($isActive): ?>
                                        <span class="chip chip-success">Active</span>
                                    <?php else: ?>
                                        <span class="chip chip-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted text-sm"><?= isset($user['created_at']) ? formatDate($user['created_at']) : 'N/A' ?></td>
                                <td>
                                    <div class="flex gap-1">
                                        <button class="btn btn-ghost btn-sm" title="Toggle role"
                                                onclick="toggleRole(<?= $user['id'] ?>, '<?= e($user['name']) ?>', '<?= $user['role'] ?>')">
                                            <?= $user['role'] === 'admin' ? '⬇ Demote' : '⬆ Promote' ?>
                                        </button>

                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <?php if ($isActive): ?>
                                                <button class="btn btn-ghost btn-sm btn-danger-text" title="Deactivate"
                                                        onclick="deactivateUser(<?= $user['id'] ?>, '<?= e($user['name']) ?>')">🚫 Deactivate</button>
                                            <?php else: ?>
                                                <button class="btn btn-ghost btn-sm" title="Activate"
                                                        onclick="activateUser(<?= $user['id'] ?>, '<?= e($user['name']) ?>')">✅ Activate</button>
                                            <?php endif; ?>

                                            <button class="btn btn-ghost btn-sm btn-danger-text" title="Delete"
                                                    onclick="deleteUser(<?= $user['id'] ?>, '<?= e($user['name']) ?>')">🗑 Delete</button>
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

    <div class="modal-overlay" id="deactivate-user-modal">
        <div class="modal glass-card" style="max-width: 400px; text-align: center;">
            <div class="modal-header" style="justify-content: center; border: none; padding: 0; margin-bottom: 20px;">
                <div style="font-size: 3rem; filter: drop-shadow(0 0 10px rgba(255, 193, 7, 0.3));">🚫</div>
            </div>
            <div class="modal-body" style="padding: 0; margin-bottom: 20px;">
                <h3 style="margin-bottom: 10px; color: #fff;">Deactivate Account</h3>
                <p class="text-muted">Are you sure you want to deactivate <span id="deactivate-modal-user-name" style="color: var(--text-primary); font-weight: 600;"></span>'s account?</p>
                <p style="color: var(--warning); font-size: 0.85rem; margin-top: 8px;">The user won't be able to log in until re-activated.</p>
                <input type="hidden" id="deactivate-modal-user-id">
            </div>
            <div class="modal-footer" style="display: flex; justify-content: center; gap: 12px; border: none; padding: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeDeactivateModal()">Cancel</button>
                <button type="button" class="btn" style="background: var(--warning); color: #000; font-weight: 600;" onclick="submitDeactivateUser()">Deactivate</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="activate-user-modal">
        <div class="modal glass-card" style="max-width: 400px; text-align: center;">
            <div class="modal-header" style="justify-content: center; border: none; padding: 0; margin-bottom: 20px;">
                <div style="font-size: 3rem; filter: drop-shadow(0 0 10px rgba(40, 167, 69, 0.3));">✅</div>
            </div>
            <div class="modal-body" style="padding: 0; margin-bottom: 20px;">
                <h3 style="margin-bottom: 10px; color: #fff;">Activate Account</h3>
                <p class="text-muted">Are you sure you want to re-activate <span id="activate-modal-user-name" style="color: var(--text-primary); font-weight: 600;"></span>'s account?</p>
                <p style="color: var(--success); font-size: 0.85rem; margin-top: 8px;">The user will regain full access to log in immediately.</p>
                <input type="hidden" id="activate-modal-user-id">
            </div>
            <div class="modal-footer" style="display: flex; justify-content: center; gap: 12px; border: none; padding: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeActivateModal()">Cancel</button>
                <button type="button" class="btn btn-success" style="font-weight: 600;" onclick="submitActivateUser()">Activate</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="delete-user-modal">
        <div class="modal glass-card" style="max-width: 400px; text-align: center;">
            <div class="modal-header" style="justify-content: center; border: none; padding: 0; margin-bottom: 20px;">
                <div style="font-size: 3rem; filter: drop-shadow(0 0 10px rgba(255, 77, 77, 0.3));">🗑️</div>
            </div>
            <div class="modal-body" style="padding: 0; margin-bottom: 20px;">
                <h3 style="margin-bottom: 10px; color: #fff;">Delete User Permanently</h3>
                <p class="text-muted">Are you sure you want to delete <span id="delete-modal-user-name" style="color: var(--text-primary); font-weight: 600;"></span>?</p>
                <p style="color: var(--danger); font-size: 0.85rem; margin-top: 8px;">⚠️ This will remove ALL related records and cannot be undone.</p>
                <input type="hidden" id="delete-modal-user-id">
            </div>
            <div class="modal-footer" style="display: flex; justify-content: center; gap: 12px; border: none; padding: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteUserModal()">Cancel</button>
                <button type="button" class="btn btn-danger" style="font-weight: 600;" onclick="submitDeleteUser()">Delete User</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="change-role-modal">
        <div class="modal glass-card" style="max-width: 400px; text-align: center;">
            <div class="modal-header" style="justify-content: center; border: none; padding: 0; margin-bottom: 20px;">
                <div id="role-modal-icon" style="font-size: 3rem; filter: drop-shadow(0 0 10px rgba(0, 242, 254, 0.3));">👑</div>
            </div>
            <div class="modal-body" style="padding: 0; margin-bottom: 20px;">
                <h3 id="role-modal-title" style="margin-bottom: 10px; color: #fff;">Change User Role</h3>
                <p class="text-muted">Are you sure you want to change <span id="role-modal-user-name" style="color: var(--text-primary); font-weight: 600;"></span>'s role to <span id="role-modal-target-role" style="text-transform: uppercase; font-weight: bold;"></span>?</p>
                <p id="role-modal-warning" style="font-size: 0.85rem; margin-top: 8px; font-weight: 500;"></p>
                <input type="hidden" id="role-modal-user-id">
                <input type="hidden" id="role-modal-new-role">
            </div>
            <div class="modal-footer" style="display: flex; justify-content: center; gap: 12px; border: none; padding: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeRoleModal()">Cancel</button>
                <button type="button" class="btn btn-primary" id="role-modal-submit-btn" style="font-weight: 600;" onclick="submitChangeRole()">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        const BASE  = document.querySelector('meta[name="base-url"]')?.content  || '';
        const TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

        function adminPost(path, body) {
            return fetch(BASE + '/' + path, {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    body + '&_token=' + encodeURIComponent(TOKEN)
            }).then(r => r.json());
        }

        // ─── دوال التحكم بنافذة تعديل الرتبة الزجاجية (Promote / Demote) ───
        function toggleRole(id, name, currentRole) {
            const modal = document.getElementById('change-role-modal');
            const newRole = currentRole === 'admin' ? 'member' : 'admin';

            document.getElementById('role-modal-user-id').value = id;
            document.getElementById('role-modal-new-role').value = newRole;
            document.getElementById('role-modal-user-name').textContent = name;
            document.getElementById('role-modal-target-role').textContent = newRole;

            const iconDiv = document.getElementById('role-modal-icon');
            const titleH3 = document.getElementById('role-modal-title');
            const warningP = document.getElementById('role-modal-warning');
            const submitBtn = document.getElementById('role-modal-submit-btn');

            if (newRole === 'admin') {
                iconDiv.textContent = '👑';
                iconDiv.style.filter = 'drop-shadow(0 0 10px rgba(0, 242, 254, 0.4))';
                titleH3.textContent = 'Promote to Admin';
                warningP.textContent = 'This user will grant full admin privileges to manage the dashboard.';
                warningP.style.color = 'var(--primary)';
                submitBtn.style.background = 'var(--primary)';
                submitBtn.style.color = '#000';
            } else {
                iconDiv.textContent = '📉';
                iconDiv.style.filter = 'drop-shadow(0 0 10px rgba(255, 77, 77, 0.4))';
                titleH3.textContent = 'Demote to Member';
                warningP.textContent = 'This user will lose all dashboard and administration access privileges.';
                warningP.style.color = 'var(--danger)';
                submitBtn.style.background = 'var(--danger)';
                submitBtn.style.color = '#fff';
            }

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeRoleModal() {
            const modal = document.getElementById('change-role-modal');
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }

        function submitChangeRole() {
            const id = document.getElementById('role-modal-user-id').value;
            const newRole = document.getElementById('role-modal-new-role').value;

            adminPost('admin/users/role', 'id=' + id + '&role=' + newRole)
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        closeRoleModal();
                        window.LuminApp?.Toast?.show(data.message || 'Role update failed.', 'error');
                    }
                })
                .catch(() => {
                    closeRoleModal();
                    window.LuminApp?.Toast?.show('Role update failed.', 'error');
                });
        }

        // ─── دوال التحكم بنافذة الـ Deactivate ───
        function deactivateUser(id, name) {
            const modal = document.getElementById('deactivate-user-modal');
            document.getElementById('deactivate-modal-user-id').value = id;
            document.getElementById('deactivate-modal-user-name').textContent = name;

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeDeactivateModal() {
            const modal = document.getElementById('deactivate-user-modal');
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }

        function submitDeactivateUser() {
            const id = document.getElementById('deactivate-modal-user-id').value;
            adminPost('admin/users/deactivate', 'id=' + id)
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        closeDeactivateModal();
                        window.LuminApp?.Toast?.show(data.message || 'Deactivate failed.', 'error');
                    }
                })
                .catch(() => {
                    closeDeactivateModal();
                    window.LuminApp?.Toast?.show('Deactivate failed.', 'error');
                });
        }

        // ─── دوال التحكم بنافذة الـ Activate ───
        function activateUser(id, name) {
            const modal = document.getElementById('activate-user-modal');
            document.getElementById('activate-modal-user-id').value = id;
            document.getElementById('activate-modal-user-name').textContent = name;

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeActivateModal() {
            const modal = document.getElementById('activate-user-modal');
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }

        function submitActivateUser() {
            const id = document.getElementById('activate-modal-user-id').value;
            adminPost('admin/users/activate', 'id=' + id)
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        closeActivateModal();
                        window.LuminApp?.Toast?.show(data.message || 'Activate failed.', 'error');
                    }
                })
                .catch(() => {
                    closeActivateModal();
                    window.LuminApp?.Toast?.show('Activate failed.', 'error');
                });
        }

        // ─── دوال التحكم بنافذة الـ Delete ───
        function deleteUser(id, name) {
            const modal = document.getElementById('delete-user-modal');
            document.getElementById('delete-modal-user-id').value = id;
            document.getElementById('delete-modal-user-name').textContent = name;

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteUserModal() {
            const modal = document.getElementById('delete-user-modal');
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }

        function submitDeleteUser() {
            const id = document.getElementById('delete-modal-user-id').value;
            adminPost('admin/users/delete', 'id=' + id)
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        closeDeleteUserModal();
                        window.LuminApp?.Toast?.show(data.message || 'Delete failed.', 'error');
                    }
                })
                .catch(() => {
                    closeDeleteUserModal();
                    window.LuminApp?.Toast?.show('Delete failed.', 'error');
                });
        }
    </script>

<?php View::includeLayout('footer'); ?>