<?php
$title = $title ?? ('Welcome to ' . APP_NAME);
$totalBooks = isset($totalBooks) ? (int)$totalBooks : 0;
$totalUsers = isset($totalUsers) ? (int)$totalUsers : 0;
$borrowedToday = isset($borrowedToday) ? (int)$borrowedToday : 0;
$featured = is_array($featured ?? null) ? $featured : [];
$categories = is_array($categories ?? null) ? $categories : [];
$quotes = is_array($quotes ?? null) ? $quotes : [];
$activities = is_array($activities ?? null) ? $activities : [];
View::includeLayout('header', ['title' => $title]);
?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="page-wrapper">
    <!-- Floating decorations -->
    <div class="floating-decorations" aria-hidden="true"></div>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="container">
            <div class="container">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:32px;">

                    <!-- LEFT: Text content (existing) -->
                    <div class="hero-content" style="flex:1; min-width:0;">
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

                    <!-- RIGHT: Girl image -->
                    <div class="hero-visual">
                        <img
                                src="<?= url('uploads/images/hero-girl.png') ?>"
                                alt="A student holding Luminara books"
                                class="hero-girl-img"
                                draggable="false"
                        >
                    </div>

                </div>

            <!-- Live Stats -->
            <div class="hero-stats">
                <div class="stat-card glass-card">
                    <div class="stat-value" data-count="<?= (int)($totalBooks ?? 0) ?>"><?= (int)($totalBooks ?? 0) ?></div>
                    <div class="stat-label">Total Books</div>
                </div>
                <div class="stat-card glass-card">
                    <div class="stat-value" data-count="<?= (int)($totalUsers ?? 0) ?>"><?= (int)($totalUsers ?? 0) ?></div>
                    <div class="stat-label">Active Readers</div>
                </div>
                <div class="stat-card glass-card">
                    <div class="stat-value" data-count="<?= (int)($borrowedToday ?? 0) ?>"><?= (int)($borrowedToday ?? 0) ?></div>
                    <div class="stat-label">Borrowed Today</div>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($quotes)): ?>
    <!-- QUOTE SECTION -->
    <section class="quote-section" data-quote-rotator>
        <div class="container">
            <?php foreach ($quotes as $idx => $q): ?>
                <blockquote class="quote-item <?= $idx === 0 ? 'active' : '' ?>">
                    <p class="quote-text">"<?= e($q['quote_text'] ?? '') ?>"</p>
                    <p class="quote-author">
                        <?php if (!empty($q['quote_author'])): ?>— <?= e($q['quote_author']) ?><?php endif; ?>
                        <?php if (!empty($q['source'])): ?> · <span><?= e($q['source']) ?></span><?php endif; ?>
                    </p>
                </blockquote>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- OUR SERVICES -->
    <section class="section services-section" id="our-services" aria-labelledby="our-services-title">
        <div class="container">
            <div class="section-header">
                <h2 id="our-services-title" class="text-gradient">Our Services</h2>
                <a href="<?= url('catalog') ?>" class="btn btn-ghost btn-sm">Explore Platform →</a>
            </div>

            <ul class="services-grid services-list">
                <li class="service-item">
                    <article class="service-card glass-card" data-service-reveal>

                        <h3 class="service-card-title">Borrow Books</h3>
                        <p class="service-card-desc">Borrow available titles quickly with transparent due dates and return reminders.</p>
                        <a class="service-card-link" href="<?= url('catalog') ?>">Start Borrowing</a>
                    </article>
                </li>

                <li class="service-item">
                    <article class="service-card glass-card" data-service-reveal>

                        <h3 class="service-card-title">Buy Books</h3>
                        <p class="service-card-desc">Purchase books you love with smooth ordering and clear order tracking.</p>
                        <a class="service-card-link" href="<?= url('catalog') ?>">Shop Books</a>
                    </article>
                </li>

                <li class="service-item">
                    <article class="service-card glass-card" data-service-reveal>

                        <h3 class="service-card-title">Audio Books</h3>
                        <p class="service-card-desc">Enjoy immersive listening sessions and continue reading wherever you are.</p>
                        <a class="service-card-link" href="<?= url('catalog') ?>">Listen Now</a>
                    </article>
                </li>

                <li class="service-item">
                    <article class="service-card glass-card" data-service-reveal>

                        <h3 class="service-card-title">Book Reviews</h3>
                        <p class="service-card-desc">Read community feedback and share your ratings to help other readers choose.</p>
                        <a class="service-card-link" href="<?= url('catalog') ?>">See Reviews</a>
                    </article>
                </li>

                <li class="service-item">
                    <article class="service-card glass-card" data-service-reveal>

                        <h3 class="service-card-title">Wishlist</h3>
                        <p class="service-card-desc">Save your next reads in one place and return anytime to continue your list.</p>
                        <a class="service-card-link" href="<?= url('user/wishlist') ?>">Open Wishlist</a>
                    </article>
                </li>


            </ul>
        </div>
    </section>

    <!-- FEATURED BOOKS -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="text-gradient">Featured Books</h2>
                <a href="<?= url('catalog') ?>" class="btn btn-ghost btn-sm">View All →</a>
            </div>

            <div class="carousel">
                <?php foreach (($featured ?? []) as $book):
                    $coverRaw = $book['cover_image'] ?? $book['cover'] ?? '';
                    if (strpos($coverRaw, 'http') === 0) {
                        $cover = $coverRaw;
                    } else {
                        $cover = get_book_cover_cached(!empty($coverRaw) ? $coverRaw : null, $book['isbn'] ?? null);
                    }
                    ?>
                    <a href="<?= url('books/' . $book['id']) ?>" class="book-card glass-card"
                       data-book-title="<?= e($book['title']) ?>"
                       data-book-url="<?= url('books/' . $book['id']) ?>">
                         <div class="book-cover">
                             <?php if (!empty($cover)): ?>
                                 <img src="<?= e($cover) ?>"
                                      alt="<?= e($book['title']) ?>"
                                      class="book-cover-img"
                                      data-book-id="<?= $book['id'] ?>"
                                      data-isbn="<?= e($book['isbn'] ?? '') ?>"
                                      onerror="this.style.display='none'; this.parentElement.innerHTML='📖';">
                             <?php else: ?>
                                 <span aria-hidden="true" style="font-size:3rem;display:flex;align-items:center;justify-content:center;width:100%;height:100%;">📖</span>
                             <?php endif; ?>
                         </div>
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



    <section class="section" style="padding-top:0;">
        <div class="container">
            <div class="section-header">
                <h2 class="text-gradient">Library Activities</h2>
            </div>

            <?php if (!empty($activities)): ?>
                <div class="book-grid activity-grid">
                    <?php
                        $exampleActivityImages = [
                            'data:image/svg+xml;charset=UTF-8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#4f46e5"/><stop offset="100%" stop-color="#7c3aed"/></linearGradient></defs><rect width="800" height="600" rx="40" fill="url(#g)"/><circle cx="620" cy="120" r="95" fill="#fff" opacity="0.18"/><rect x="120" y="360" width="560" height="130" rx="28" fill="#111827" opacity="0.28"/><text x="70" y="95" fill="#fff" font-size="44" font-family="Arial, Helvetica, sans-serif" font-weight="700">Monthly Reading Circle</text><text x="70" y="170" fill="#eef2ff" font-size="24" font-family="Arial, Helvetica, sans-serif">Join fellow readers for a guided discussion.</text><circle cx="230" cy="260" r="78" fill="#fde68a"/><circle cx="390" cy="260" r="78" fill="#fca5a5"/><circle cx="550" cy="260" r="78" fill="#86efac"/></svg>'),
                            'data:image/svg+xml;charset=UTF-8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#0f766e"/><stop offset="100%" stop-color="#14b8a6"/></linearGradient></defs><rect width="800" height="600" rx="40" fill="url(#g)"/><circle cx="640" cy="140" r="100" fill="#fff" opacity="0.16"/><text x="70" y="95" fill="#fff" font-size="44" font-family="Arial, Helvetica, sans-serif" font-weight="700">Storytelling Workshop</text><text x="70" y="170" fill="#ecfeff" font-size="24" font-family="Arial, Helvetica, sans-serif">Explore creative ways to lead family story sessions.</text><rect x="120" y="250" width="110" height="220" rx="16" fill="#f59e0b"/><rect x="250" y="220" width="110" height="250" rx="16" fill="#60a5fa"/><rect x="380" y="190" width="110" height="280" rx="16" fill="#fb7185"/><rect x="510" y="240" width="110" height="230" rx="16" fill="#facc15"/></svg>'),
                            'data:image/svg+xml;charset=UTF-8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600"><defs><linearGradient id="g" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#312e81"/><stop offset="100%" stop-color="#1e3a8a"/></linearGradient></defs><rect width="800" height="600" rx="40" fill="url(#g)"/><circle cx="630" cy="130" r="90" fill="#fde68a" opacity="0.35"/><text x="70" y="95" fill="#fff" font-size="44" font-family="Arial, Helvetica, sans-serif" font-weight="700">Library Tour Day</text><text x="70" y="170" fill="#e0e7ff" font-size="24" font-family="Arial, Helvetica, sans-serif">Welcome members into study spaces and archives.</text><rect x="80" y="230" width="80" height="250" rx="18" fill="#f8fafc"/><rect x="180" y="230" width="80" height="250" rx="18" fill="#f59e0b"/><rect x="280" y="230" width="80" height="250" rx="18" fill="#60a5fa"/><rect x="380" y="230" width="80" height="250" rx="18" fill="#f472b6"/><rect x="480" y="230" width="80" height="250" rx="18" fill="#34d399"/><rect x="580" y="230" width="80" height="250" rx="18" fill="#facc15"/></svg>'),
                        ];
                    ?>
                    <?php foreach ($activities as $index => $activity): ?>
                        <?php
                            $activityImage = !empty($activity['image_path'])
                                ? url(ltrim($activity['image_path'], '/'))
                                : url($exampleActivityImages[$index % count($exampleActivityImages)]);
                        ?>
                        <article class="activity-card glass-card">
                            <div class="activity-cover">
                                <img src="<?= e($activityImage) ?>" alt="<?= e($activity['title'] ?? 'Activity') ?>" loading="lazy">
                            </div>
                            <div class="book-info">
                                <h4 class="book-title"><?= e($activity['title'] ?? '') ?></h4>
                                <p class="book-author"><?= e($activity['description'] ?? '') ?></p>
                                <div class="book-meta">
                                    <span class="chip chip-primary"><?= !empty($activity['activity_date']) ? formatDate($activity['activity_date']) : 'Upcoming' ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-muted" style="padding:32px 0;">No activities announced yet. Check back soon.</div>
            <?php endif; ?>
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
                <?php foreach (($categories ?? []) as $cat): ?>
                    <a href="<?= url('catalog?category=' . $cat['id']) ?>" class="category-card glass-card">

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
