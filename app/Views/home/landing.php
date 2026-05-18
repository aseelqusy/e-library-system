<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-wrapper">
    <!-- Floating decorations -->
    <div class="floating-decorations" aria-hidden="true"></div>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    Discover Your Next <span class="text-gradient">Great Read</span>
                </h1>
                <p class="hero-subtitle">
                    Welcome to <?= e(APP_NAME) ?> — a curated universe of knowledge, stories, and imagination.
                    Explore thousands of books, borrow with ease, and join a community of passionate readers.
                </p>
                <div class="hero-actions">
                    <a href="<?= url('catalog') ?>" class="btn btn-primary btn-lg">
                        📚 Browse Catalog
                    </a>
                    <?php if (!Auth::check()): ?>
                        <a href="<?= url('register') ?>" class="btn btn-outline btn-lg">
                            ✨ Join Free
                        </a>
                    <?php else: ?>
                        <a href="<?= url('user/borrows') ?>" class="btn btn-secondary btn-lg">
                            📖 My Books
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Live Stats -->
            <div class="hero-stats">
                <div class="stat-card glass-card">
                    <div class="stat-value" data-count="<?= $totalBooks ?>"><?= $totalBooks ?></div>
                    <div class="stat-label">Total Books</div>
                </div>
                <div class="stat-card glass-card">
                    <div class="stat-value" data-count="<?= $totalUsers ?>"><?= $totalUsers ?></div>
                    <div class="stat-label">Active Readers</div>
                </div>
                <div class="stat-card glass-card">
                    <div class="stat-value" data-count="<?= $borrowedToday ?>"><?= $borrowedToday ?></div>
                    <div class="stat-label">Borrowed Today</div>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($quote)): ?>
    <!-- QUOTE SECTION -->
    <section class="quote-section">
        <div class="container">
            <p class="quote-text">"<?= e($quote['text']) ?>"</p>
            <?php if (!empty($quote['author'])): ?>
                <p class="quote-author">— <?= e($quote['author']) ?></p>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- FEATURED BOOKS -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="text-gradient">Featured Books</h2>
                <a href="<?= url('catalog') ?>" class="btn btn-ghost btn-sm">View All →</a>
            </div>

            <div class="carousel">
                <?php foreach ($featured as $book): ?>
                    <?php $cover = get_book_cover_cached($book['cover_image'] ?? null, $book['isbn'] ?? null); ?>
                    <a href="<?= url('books/' . $book['id']) ?>" class="book-card glass-card"
                       data-book-title="<?= e($book['title']) ?>"
                       data-book-url="<?= url('books/' . $book['id']) ?>">
                        <?php if (!empty($cover)): ?>
                            <div class="book-cover"><img src="<?= e($cover) ?>" alt="<?= e($book['title']) ?>" class="book-cover-img"></div>
                        <?php else: ?>
                            <div class="book-cover" aria-hidden="true">📖</div>
                        <?php endif; ?>
                        <div class="book-info">
                            <h4 class="book-title"><?= e($book['title']) ?></h4>
                            <p class="book-author"><?= e($book['author']) ?></p>
                            <div class="book-meta">
                                <span class="book-rating">★ <?= $book['rating'] ?></span>
                                <span class="book-availability <?= $book['available'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                                    <?= $book['available'] > 0 ? 'Available' : 'Borrowed' ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-overlay">
                            <span class="btn btn-primary btn-sm">View Details</span>
                            <span class="btn btn-outline btn-sm">Quick Preview</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CATEGORIES -->
    <section class="section" style="background: var(--bg-secondary);">
        <div class="container">
            <div class="section-header">
                <h2 class="text-gradient">Browse by Category</h2>
                <a href="<?= url('catalog/categories') ?>" class="btn btn-ghost btn-sm">All Categories →</a>
            </div>

            <div class="category-grid">
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= url('catalog?category=' . $cat['id']) ?>" class="category-card glass-card">
                        <div class="cat-icon"><?= $cat['icon'] ?></div>
                        <div class="cat-name"><?= e($cat['name']) ?></div>
                        <div class="cat-count">Explore collection</div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="section text-center">
        <div class="container container-md">
            <h2 class="text-gradient mb-4">Ready to Start Reading?</h2>
            <p class="text-muted mb-6" style="max-width:500px;margin-left:auto;margin-right:auto;">
                Join our community of book lovers. Create a free account and start borrowing today.
            </p>
            <?php if (!Auth::check()): ?>
                <a href="<?= url('register') ?>" class="btn btn-primary btn-lg">Create Free Account</a>
            <?php else: ?>
                <a href="<?= url('catalog') ?>" class="btn btn-primary btn-lg">Explore Catalog</a>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php View::includeLayout('footer'); ?>
