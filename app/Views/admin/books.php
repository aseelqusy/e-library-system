<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

    <div class="admin-layout">
        <?php View::partial('admin/sidebar'); ?>

        <main class="admin-main">
            <div class="flex items-center justify-between mb-6" style="justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div>
                    <h2>📚 Manage Books</h2>
                    <p class="text-muted text-sm"><?= count($books) ?> books in collection</p>
                </div>
                <button class="btn btn-primary" onclick="document.getElementById('add-book-modal').classList.add('show')">+ Add New Book</button>
            </div>

            <!-- Filters -->
            <div class="filter-bar glass-card mb-4">
                <div class="search-box">
                    <input type="text" id="book-search" class="form-control" placeholder="Search books by title, author, ISBN…">
                </div>
                <select class="form-control" id="filter-category" style="max-width:180px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Books Table -->
            <div class="card glass-card">
                <div class="table-wrapper">
                    <table id="books-table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>ISBN</th>
                            <th>Available</th>
                            <th>Rating</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($books as $i => $book): ?>
                            <?php
                            $cat = !empty($book['category_id']) ? Category::find((int)$book['category_id']) : null;
                            $searchText = strtolower($book['title'] . ' ' . $book['author'] . ' ' . ($book['isbn'] ?? ''));
                            ?>
                            <tr data-category="<?= $book['category_id'] ?>" data-search="<?= e($searchText) ?>">
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span class="book-thumb">📕</span>
                                        <div>
                                            <strong><?= e($book['title']) ?></strong>
                                            <div class="text-muted text-sm"><?= $book['pages'] ?> pages · <?= $book['year'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e($book['author']) ?></td>
                                <td><span class="chip chip-outline"><?= $cat ? e($cat['name']) : 'N/A' ?></span></td>
                                <td class="text-muted text-sm"><?= e($book['isbn']) ?></td>
                                <td>
                                <span class="chip <?= $book['available'] > 0 ? 'chip-success' : 'chip-danger' ?>">
                                    <?= $book['available'] ?>/<?= $book['copies'] ?>
                                </span>
                                </td>
                                <td><?= rating_stars($book['rating']) ?></td>
                                <td>
                                    <div class="flex gap-1">
                                        <a href="<?= url('books/' . $book['id']) ?>" class="btn btn-ghost btn-sm" title="View">👁</a>
                                        <?php
                                        $coverPath = $book['cover_image'] ?? $book['cover'] ?? '';
                                        $pdfPath   = $book['pdf_file'] ?? '';
                                        ?>
                                        <button class="btn btn-ghost btn-sm" title="Edit"
                                                data-id="<?= $book['id'] ?>"
                                                data-title="<?= e($book['title']) ?>"
                                                data-author="<?= e($book['author']) ?>"
                                                data-isbn="<?= e($book['isbn']) ?>"
                                                data-category="<?= $book['category_id'] ?>"
                                                data-year="<?= $book['year'] ?>"
                                                data-pages="<?= $book['pages'] ?>"
                                                data-copies="<?= $book['copies'] ?>"
                                                data-publisher="<?= e($book['publisher'] ?? '') ?>"
                                                data-description="<?= e($book['description'] ?? '') ?>"
                                                data-cover="<?= e($coverPath) ?>"
                                                data-pdf="<?= e($pdfPath) ?>"
                                                onclick="editBook(this)">✏️</button>
                                        <button class="btn btn-ghost btn-sm btn-danger-text" title="Delete" onclick="deleteBook(<?= $book['id'] ?>, '<?= e($book['title']) ?>')">🗑</button>
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

    <!-- Add Book Modal -->
    <div class="modal-overlay" id="add-book-modal">
        <div class="modal" style="max-width:600px;">
            <div class="modal-header">
                <h3>Add New Book</h3>
                <button class="btn btn-ghost btn-icon modal-close" onclick="this.closest('.modal-overlay').classList.remove('show'); document.body.style.overflow='';">✕</button>
            </div>
            <form action="<?= url('admin/books/store') ?>" method="POST" enctype="multipart/form-data">
                <?= Csrf::field() ?>
                <div class="modal-body">
                    <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group" style="grid-column:1/-1;">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required placeholder="Enter book title">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Author *</label>
                            <input type="text" name="author" class="form-control" required placeholder="Author name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">ISBN *</label>
                            <input type="text" name="isbn" class="form-control" required placeholder="978-…">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" class="form-control" placeholder="2024" min="1000" max="2099">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pages</label>
                            <input type="number" name="pages" class="form-control" placeholder="300" min="1">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Copies</label>
                            <input type="number" name="copies" class="form-control" placeholder="5" min="0" value="1">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Publisher</label>
                            <input type="text" name="publisher" class="form-control" placeholder="Publisher name">
                        </div>
                        <div class="form-group" style="grid-column:1/-1;">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Brief description…"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cover Image</label>
                            <input type="file" name="cover_image" class="form-control" accept="image/*">
                            <div class="text-muted text-sm">JPG, PNG, GIF, or WEBP. Max 5MB.</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Book PDF</label>
                            <input type="file" name="pdf_file" class="form-control" accept="application/pdf">
                            <div class="text-muted text-sm">PDF only. Max 25MB.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="this.closest('.modal-overlay').classList.remove('show'); document.body.style.overflow='';">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Book</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Book Modal -->
    <div class="modal-overlay" id="edit-book-modal">
        <div class="modal" style="max-width:600px;">
            <div class="modal-header">
                <h3>Edit Book</h3>
                <button class="btn btn-ghost btn-icon modal-close" onclick="this.closest('.modal-overlay').classList.remove('show'); document.body.style.overflow='';">✕</button>
            </div>
            <form action="<?= url('admin/books/update') ?>" method="POST" id="edit-book-form" enctype="multipart/form-data">
                <?= Csrf::field() ?>
                <input type="hidden" name="id" id="edit-book-id">
                <div class="modal-body">
                    <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group" style="grid-column:1/-1;">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" id="edit-book-title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Author *</label>
                            <input type="text" name="author" id="edit-book-author" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">ISBN *</label>
                            <input type="text" name="isbn" id="edit-book-isbn" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category_id" id="edit-book-category" class="form-control">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" id="edit-book-year" class="form-control" min="1000" max="2099">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pages</label>
                            <input type="number" name="pages" id="edit-book-pages" class="form-control" min="1">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Copies</label>
                            <input type="number" name="copies" id="edit-book-copies" class="form-control" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Publisher</label>
                            <input type="text" name="publisher" id="edit-book-publisher" class="form-control">
                        </div>
                        <div class="form-group" style="grid-column:1/-1;">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit-book-description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Replace Cover Image</label>
                            <div id="edit-cover-preview" style="display:none;margin-bottom:8px;">
                                <img id="edit-cover-img" src="" alt="Current cover"
                                     style="max-height:80px;border-radius:6px;border:1px solid rgba(255,255,255,0.15);">
                                <div class="text-muted text-sm" style="margin-top:4px;">Current cover — upload a new one to replace it</div>
                            </div>
                            <input type="file" name="cover_image" class="form-control" accept="image/*">
                            <div class="text-muted text-sm">JPG, PNG, GIF, or WEBP. Max 5MB.</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Replace Book PDF</label>
                            <div id="edit-pdf-preview" style="display:none;margin-bottom:8px;">
                                <span style="font-size:1.4rem;">📄</span>
                                <span id="edit-pdf-name" class="text-muted text-sm" style="margin-left:6px;"></span>
                                <div class="text-muted text-sm" style="margin-top:2px;">Current PDF — upload a new one to replace it</div>
                            </div>
                            <input type="file" name="pdf_file" class="form-control" accept="application/pdf">
                            <div class="text-muted text-sm">PDF only. Max 25MB.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="this.closest('.modal-overlay').classList.remove('show'); document.body.style.overflow='';">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Table search
            const searchInput = document.getElementById('book-search');
            const categoryFilter = document.getElementById('filter-category');
            const rows = document.querySelectorAll('#books-table tbody tr');

            function filterTable() {
                const q = searchInput.value.toLowerCase();
                const cat = categoryFilter.value;
                rows.forEach(row => {
                    const matchSearch = !q || row.dataset.search.includes(q);
                    const matchCat = !cat || row.dataset.category === cat;
                    row.style.display = (matchSearch && matchCat) ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterTable);
            categoryFilter.addEventListener('change', filterTable);
        });

        function editBook(button) {
            const modal = document.getElementById('edit-book-modal');
            document.getElementById('edit-book-id').value          = button.dataset.id;
            document.getElementById('edit-book-title').value       = button.dataset.title;
            document.getElementById('edit-book-author').value      = button.dataset.author;
            document.getElementById('edit-book-isbn').value        = button.dataset.isbn;
            document.getElementById('edit-book-category').value    = button.dataset.category || '';
            document.getElementById('edit-book-year').value        = button.dataset.year || '';
            document.getElementById('edit-book-pages').value       = button.dataset.pages || '';
            document.getElementById('edit-book-copies').value      = button.dataset.copies || '';
            document.getElementById('edit-book-publisher').value   = button.dataset.publisher || '';
            document.getElementById('edit-book-description').value = button.dataset.description || '';

            // Cover preview
            const coverPath   = button.dataset.cover || '';
            const coverPreview = document.getElementById('edit-cover-preview');
            const coverImg    = document.getElementById('edit-cover-img');
            if (coverPath) {
                const base = document.querySelector('meta[name="base-url"]')?.content || '';
                coverImg.src = base + '/' + coverPath;
                coverPreview.style.display = 'block';
            } else {
                coverImg.src = '';
                coverPreview.style.display = 'none';
            }

            // PDF preview
            const pdfPath    = button.dataset.pdf || '';
            const pdfPreview = document.getElementById('edit-pdf-preview');
            const pdfName    = document.getElementById('edit-pdf-name');
            if (pdfPath) {
                pdfName.textContent = pdfPath.split('/').pop();
                pdfPreview.style.display = 'block';
            } else {
                pdfPreview.style.display = 'none';
            }

            // Clear any previously selected files so old selection doesn't linger
            document.querySelectorAll('#edit-book-form input[type="file"]').forEach(f => f.value = '');

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function deleteBook(id, title) {
            if (!confirm('Delete "' + title + '"? This action cannot be undone.')) return;

            const base = document.querySelector('meta[name="base-url"]')?.content || '';
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

            fetch(base + '/admin/books/delete', {
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