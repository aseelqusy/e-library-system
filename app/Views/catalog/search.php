<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-wrapper" style="padding-top: var(--navbar-height);">
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="text-gradient">Search Results</h2>
            </div>

            <form action="<?= url('catalog/search') ?>" method="GET" class="mb-6">
                <div class="input-group" style="max-width:500px;">
                    <span class="input-icon">🔍</span>
                    <input type="text" name="q" class="form-control" placeholder="Search books..."
                           value="<?= e($query) ?>" autofocus aria-label="Search books">
                </div>
            </form>

            <?php if ($query): ?>
                <p class="text-muted mb-4"><?= count($books) ?> result(s) for "<strong><?= e($query) ?></strong>"</p>

                <?php if (empty($books)): ?>
                    <div class="text-center" style="padding:60px 0;">
                        <p style="font-size:3rem;margin-bottom:12px;">📭</p>
                        <h3>No books found</h3>
                        <p class="text-muted">Try searching with different keywords.</p>
                    </div>
                <?php else: ?>
                    <div class="book-grid">
                        <?php foreach ($books as $book): ?>
                            <?php $coverPath = !empty($book['cover_image']) ? url(ltrim($book['cover_image'], '/')) : null; ?>
                            <a href="<?= url('books/' . $book['id']) ?>" class="book-card glass-card">
                                <div class="book-cover">
                                    <?php if ($coverPath): ?>
                                        <img src="<?= $coverPath ?>" alt="<?= e($book['title']) ?> cover" loading="lazy">
                                    <?php else: ?>
                                        📖
                                    <?php endif; ?>
                                </div>
                                <div class="book-info">
                                    <h4 class="book-title"><?= e($book['title']) ?></h4>
                                    <p class="book-author"><?= e($book['author']) ?></p>
                                    <div class="book-meta">
                                        <span class="book-rating">★ <?= $book['rating'] ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center" style="padding:60px 0;">
                    <p style="font-size:3rem;margin-bottom:12px;">🔍</p>
                    <h3>Search our library</h3>
                    <p class="text-muted">Find books by title, author, or keyword.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php View::includeLayout('footer'); ?>
