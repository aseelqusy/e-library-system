<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-wrapper" style="padding-top: var(--navbar-height);">
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="text-gradient">My Wishlist</h2>
                <span class="text-muted text-sm"><?= count($books) ?> books saved</span>
            </div>

            <?php if (empty($books)): ?>
                <div class="text-center" style="padding:60px 0;">
                    <p style="font-size:3rem;margin-bottom:12px;">❤️</p>
                    <h3>Your wishlist is empty</h3>
                    <p class="text-muted">Add books you'd like to read later.</p>
                    <a href="<?= url('catalog') ?>" class="btn btn-primary mt-4">Browse Catalog</a>
                </div>
            <?php else: ?>
                <div class="book-grid">
                    <?php foreach ($books as $book): ?>
                        <div class="book-card glass-card" style="position:relative;">
                            <a href="<?= url('books/' . $book['id']) ?>">
                                <div class="book-cover">📖</div>
                                <div class="book-info">
                                    <h4 class="book-title"><?= e($book['title']) ?></h4>
                                    <p class="book-author"><?= e($book['author']) ?></p>
                                    <div class="book-meta">
                                        <span class="book-rating">★ <?= $book['rating'] ?></span>
                                        <span class="book-availability <?= $book['available'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                                            <?= $book['available'] > 0 ? 'Available' : 'Unavailable' ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
                            <button class="btn btn-icon btn-ghost wishlist-btn active"
                                    data-book-id="<?= $book['id'] ?>"
                                    style="position:absolute;top:8px;right:8px;z-index:2;font-size:1.2rem;"
                                    aria-label="Remove from wishlist"
                                    title="Remove from wishlist">❤</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php View::includeLayout('footer'); ?>
