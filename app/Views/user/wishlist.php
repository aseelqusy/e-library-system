<?php $pageTitle = 'My Wishlist'; if (isset($title)) { $pageTitle = $title; } ?>
<?php View::includeLayout('header', ['title' => $pageTitle]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="page-wrapper user-dashboard-page" style="padding-top: var(--navbar-height);">
    <!-- Floating decorations -->
    <div class="floating-decorations" aria-hidden="true"></div>
    <section class="section dashboard-section">
        <div class="container">
            <div class="section-header dashboard-page-header">
                <div>
                    <h2 class="text-gradient">My Wishlist</h2>
                    <p class="text-muted text-sm"><?= count($books) ?> books saved for later.</p>
                </div>
            </div>

            <div class="dashboard-container">
                <?php View::partial('user.partials.sidebar', ['current_page' => $current_page ?? 'wishlist']); ?>

                <main class="dashboard-main">
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
                                <?php
                                    $coverPath = getBookCover($book);
                                ?>
                                <div class="book-card glass-card" style="position:relative;">
                                    <a href="<?= url('books/' . $book['id']) ?>">
                                        <div class="book-cover">
                                            <?php if ($coverPath): ?>
                                                <img src="<?= e($coverPath) ?>"
                                                     alt="<?= e($book['title']) ?> cover"
                                                     loading="lazy"
                                                     data-book-id="<?= $book['id'] ?>"
                                                     data-isbn="<?= e($book['isbn'] ?? '') ?>"
                                                     style="width:100%; height:100%; object-fit:cover;"
                                                     onerror="this.style.display='none'; this.parentElement.innerHTML='📖';">
                                            <?php else: ?>
                                                <span style="font-size:3rem;display:flex;align-items:center;justify-content:center;width:100%;height:100%;">📖</span>
                                            <?php endif; ?>
                                        </div>
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
                </main>
            </div>
        </div>
    </section>
</div>

<?php View::includeLayout('footer'); ?>
