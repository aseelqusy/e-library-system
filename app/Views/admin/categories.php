<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="admin-layout">
    <?php View::partial('admin/sidebar'); ?>

    <main class="admin-main">
        <div class="flex items-center justify-between mb-6" style="justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2>🏷️ Manage Categories</h2>
                <p class="text-muted text-sm"><?= count($categories) ?> categories</p>
            </div>
            <button class="btn btn-primary"
                    onclick="document.getElementById('add-cat-modal').classList.add('show'); document.body.style.overflow='hidden';">
                + Add Category
            </button>
        </div>

        <!-- Categories Grid -->
        <div class="book-grid" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr));">
            <?php foreach ($categories as $cat): ?>
            <div class="card glass-card" style="padding:24px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h3><?= e($cat['name']) ?></h3>
                        <p class="text-muted text-sm mb-2"><?= e($cat['description'] ?? '') ?></p>
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
                    <button class="btn btn-ghost btn-sm btn-danger-text"
                            onclick="deleteCategory(<?= $cat['id'] ?>, '<?= e($cat['name']) ?>')">🗑 Delete</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<!-- ── Add Category Modal ─────────────────────────────────────── -->
<div class="modal-overlay" id="add-cat-modal">
    <div class="modal" style="max-width:450px;">
        <div class="modal-header">
            <h3>Add New Category</h3>
            <button class="btn btn-ghost btn-icon modal-close"
                    onclick="closeModal('add-cat-modal')">✕</button>
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
                <button type="button" class="btn btn-secondary"
                        onclick="closeModal('add-cat-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Category</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Edit Category Modal ────────────────────────────────────── -->
<div class="modal-overlay" id="edit-cat-modal">
    <div class="modal" style="max-width:450px;">
        <div class="modal-header">
            <h3>Edit Category</h3>
            <button class="btn btn-ghost btn-icon modal-close"
                    onclick="closeModal('edit-cat-modal')">✕</button>
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
                <button type="button" class="btn btn-secondary"
                        onclick="closeModal('edit-cat-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
const BASE  = document.querySelector('meta[name="base-url"]')?.content  || '';
const TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
    document.body.style.overflow = '';
}

// Close modal on backdrop click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

function editCategory(button) {
    document.getElementById('edit-cat-id').value          = button.dataset.id;
    document.getElementById('edit-cat-name').value        = button.dataset.name;
    document.getElementById('edit-cat-description').value = button.dataset.description || '';
    document.getElementById('edit-cat-icon').value        = button.dataset.icon        || '';

    document.getElementById('edit-cat-modal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

document.getElementById('edit-cat-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.currentTarget;
    const data = new URLSearchParams(new FormData(form));
    // FormData picks up the hidden _token field from Csrf::field()
    // but also make sure the meta token is included as fallback
    if (!data.has('_token') || !data.get('_token')) {
        data.set('_token', TOKEN);
    }

    fetch(BASE + '/admin/categories/update', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    data.toString()
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            window.location.reload();
        } else {
            window.LuminApp?.Toast?.show(res.message || 'Update failed.', 'error');
        }
    })
    .catch(() => window.LuminApp?.Toast?.show('Update failed.', 'error'));
});

function deleteCategory(id, name) {
    if (!confirm('Delete category "' + name + '"? Books in this category will become uncategorized.')) return;

    fetch(BASE + '/admin/categories/delete', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    'id=' + id + '&_token=' + encodeURIComponent(TOKEN)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            window.location.reload();
        } else {
            window.LuminApp?.Toast?.show(res.message || 'Delete failed.', 'error');
        }
    })
    .catch(() => window.LuminApp?.Toast?.show('Delete failed.', 'error'));
}
</script>

<?php View::includeLayout('footer'); ?>
