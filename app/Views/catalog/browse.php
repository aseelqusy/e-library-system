<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

    <div class="page-wrapper" style="padding-top: var(--navbar-height);">
        <div class="floating-decorations" aria-hidden="true"></div>

        <section class="section">
            <div class="container">
                <div class="section-header">
                    <h2 class="text-gradient">Browse Catalog</h2>
                    <span class="text-muted text-sm"><?= count($books) ?> books found</span>
                </div>

                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="input-group search-input">
                        <span class="input-icon">🔍</span>
                        <input type="text" id="catalog-search" class="form-control"
                               placeholder="Search by title or author..."
                               value="<?= e($filters['q']) ?>"
                               aria-label="Search books">
                    </div>

                    <select class="form-control" id="filter-category" onchange="location.href='<?= url('catalog') ?>?category='+this.value+'&sort=<?= e($filters['sort']) ?>'" aria-label="Filter by category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $filters['category'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['icon'] . ' ' . $cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select class="form-control" id="filter-sort" onchange="location.href='<?= url('catalog') ?>?category=<?= e($filters['category']) ?>&sort='+this.value" aria-label="Sort by">
                        <option value="newest" <?= $filters['sort']==='newest'?'selected':'' ?>>Newest First</option>
                        <option value="title"  <?= $filters['sort']==='title'?'selected':'' ?>>Title A-Z</option>
                        <option value="rating" <?= $filters['sort']==='rating'?'selected':'' ?>>Highest Rated</option>
                        <option value="year"   <?= $filters['sort']==='year'?'selected':'' ?>>Publication Year</option>
                    </select>
                </div>

                <!-- Book Grid -->
                <div class="book-grid" id="book-grid">
                    <?php if (empty($books)): ?>
                        <div class="empty-state text-center" style="grid-column:1/-1; padding:60px 0;">
                            <p style="font-size:3rem;margin-bottom:12px;">📭</p>
                            <h3>No books found</h3>
                            <p class="text-muted">Try a different search or filter.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($books as $book):
                            $catName = $categories[$book['category_id']]['name'] ?? 'General';
                            $coverPath = get_book_cover_cached($book['cover_image'] ?? $book['cover'] ?? null, $book['isbn'] ?? null);
                            ?>
                            <a href="<?= url('books/' . $book['id']) ?>" class="book-card glass-card"
                               data-title="<?= e($book['title']) ?>"
                               data-author="<?= e($book['author']) ?>"
                               data-book-title="<?= e($book['title']) ?>"
                               data-book-url="<?= url('books/' . $book['id']) ?>">
                                <div class="book-cover" style="display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                                    <?php if ($coverPath): ?>
                                        <img src="<?= $coverPath ?>"
                                             alt="<?= e($book['title']) ?> cover"
                                             loading="lazy"
                                             onerror="this.style.display='none'; this.parentElement.innerHTML='📖';">
                                    <?php else: ?>
                                        📖
                                    <?php endif; ?>
                                </div>
                                <div class="book-info">
                                    <h4 class="book-title"><?= e($book['title']) ?></h4>
                                    <p class="book-author"><?= e($book['author']) ?></p>
                                    <div class="book-meta">
                                        <span class="book-rating">★ <?= $book['rating'] ?></span>
                                        <span class="book-availability <?= $book['available'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                                        <?= $book['available'] > 0 ? $book['available'] . ' left' : 'Unavailable' ?>
                                    </span>
                                    </div>
                                </div>
                                <div class="card-overlay">
                                    <span class="chip chip-primary"><?= e($catName) ?></span>
                                    <span class="btn btn-primary btn-sm" style="margin-top:8px;">View Details</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Empty state for JS search filtering -->
                    <div class="empty-state text-center" id="no-results"
                         style="grid-column:1/-1;padding:60px 0;display:none;">
                        <p style="font-size:3rem;margin-bottom:12px;">🔍</p>
                        <h3>No matches</h3>
                        <p class="text-muted">Try a different search term.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

<?php View::includeLayout('footer'); ?>