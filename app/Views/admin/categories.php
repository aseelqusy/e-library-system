<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="admin-layout">
    <?php View::partial('admin/sidebar'); ?>

    <main class="admin-main">
        <div class="flex items-center justify-between mb-6" style="justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2>🏷️ Manage Categories</h2>
                <p class="text-muted text-sm"><?= count($categories) ?> categories</p>
            </div>
            <button class="btn btn-primary" onclick="document.getElementById('add-cat-modal').classList.add('active')">+ Add Category</button>
        </div>

        <!-- Categories Grid -->
        <div class="book-grid" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr));">
            <?php foreach ($categories as $cat): ?>
            <div class="card glass-card" style="padding:24px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h3><?= e($cat['name']) ?></h3>
                        <p class="text-muted text-sm mb-2"><?= e($cat['description']) ?></p>
                        <span class="chip chip-outline"><?= $counts[$cat['id']] ?? 0 ?> books</span>
                    </div>
                    <span style="font-size:32px;"><?= $cat['icon'] ?? '📁' ?></span>
                </div>
                <div class="flex gap-1 mt-4">
                    <button class="btn btn-ghost btn-sm"
                            data-id="<?= $cat['id'] ?>"
                            data-name="<?= e($cat['name']) ?>"
                            data-description="<?= e($cat['description'] ?? '') ?>"
                            data-icon="<?= e($cat['icon'] ?? '') ?>"
                            onclick="editCategory(this)">✏️ Edit</button>
                    <button class="btn btn-ghost btn-sm btn-danger-text" onclick="deleteCategory(<?= $cat['id'] ?>, '<?= e($cat['name']) ?>')">🗑 Delete</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<!-- Add Category Modal -->
<div class="modal" id="add-cat-modal">
    <div class="modal-overlay" onclick="this.parentElement.classList.remove('active')"></div>
    <div class="modal-content glass-card" style="max-width:450px;">
        <div class="modal-header">
            <h3>Add New Category</h3>
            <button class="btn btn-ghost btn-icon modal-close" onclick="this.closest('.modal').classList.remove('active')">✕</button>
        </div>
        <form action="<?= url('admin/categories/store') ?>" method="POST">
            <?= Csrf::field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Biography">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Brief description…"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Icon (emoji)</label>
                    <input type="text" name="icon" class="form-control" placeholder="📁" maxlength="4">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal" id="edit-cat-modal">
    <div class="modal-overlay" onclick="this.parentElement.classList.remove('active')"></div>
    <div class="modal-content glass-card" style="max-width:450px;">
        <div class="modal-header">
            <h3>Edit Category</h3>
            <button class="btn btn-ghost btn-icon modal-close" onclick="this.closest('.modal').classList.remove('active')">✕</button>
        </div>
        <form id="edit-cat-form">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" id="edit-cat-id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" id="edit-cat-name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit-cat-description" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Icon (emoji)</label>
                    <input type="text" name="icon" id="edit-cat-icon" class="form-control" maxlength="4">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(button) {
    document.getElementById('edit-cat-id').value = button.dataset.id;
    document.getElementById('edit-cat-name').value = button.dataset.name;
    document.getElementById('edit-cat-description').value = button.dataset.description || '';
    document.getElementById('edit-cat-icon').value = button.dataset.icon || '';
    document.getElementById('edit-cat-modal').classList.add('active');
}

document.getElementById('edit-cat-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const base = document.querySelector('meta[name="base-url"]')?.content || '';
    const form = e.currentTarget;
    const data = new URLSearchParams(new FormData(form));

    fetch(base + '/admin/categories/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: data.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            window.LuminApp?.Toast?.show(data.message || 'Update failed.', 'error');
        }
    })
    .catch(() => window.LuminApp?.Toast?.show('Update failed.', 'error'));
});

function deleteCategory(id, name) {
    if (!confirm('Delete category "' + name + '"? Books in this category will become uncategorized.')) return;

    const base = document.querySelector('meta[name="base-url"]')?.content || '';
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

    fetch(base + '/admin/categories/delete', {
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
