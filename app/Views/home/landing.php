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
                    // Normalize cover URL/path. Support absolute http URLs, stored upload paths, or cached covers.
                    if (strpos($coverRaw, 'http') === 0) {
                        $cover = $coverRaw;
                    } elseif (!empty($coverRaw) && (strpos($coverRaw, 'uploads/') === 0 || strpos($coverRaw, '/') === 0)) {
                        // If the stored path already points to uploads or starts with a slash, build a full URL
                        $cover = url(ltrim($coverRaw, '/'));
                    } else {
                        // Fallback to cached helper which may return a filename or path
                        $cover = get_book_cover_cached(!empty($coverRaw) ? $coverRaw : null, $book['isbn'] ?? null);
                        // If helper returned a relative filename, assume it's under uploads/covers
                        if (!empty($cover) && strpos($cover, 'http') !== 0 && strpos($cover, '/') !== 0) {
                            $cover = url('uploads/covers/' . ltrim($cover, '/'));
                        } elseif (!empty($cover) && (strpos($cover, 'uploads/') === 0 || strpos($cover, '/') === 0)) {
                            $cover = url(ltrim($cover, '/'));
                        }
                    }
                    ?>
                    <a href="<?= url('books/' . $book['id']) ?>" class="book-card glass-card"
                       data-book-title="<?= e($book['title']) ?>"
                       data-book-url="<?= url('books/' . $book['id']) ?>">
                         <div class="book-cover">
                             <?php if (!empty($cover)): ?>
                                  <img src="<?= e($cover) ?>"
                                       alt="<?= e($book['title']) ?>"
                                       class="book-cover-img landing-cover-img"
                                       data-book-id="<?= $book['id'] ?>"
                                       data-isbn="<?= e($book['isbn'] ?? '') ?>">
                                  <div class="landing-cover-fallback" style="width:100%;height:100%;display:none;align-items:center;justify-content:center;font-size:3rem;">📖</div>
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
                        // Use actual images from uploads/images as fallbacks for activity cards
                        $exampleActivityImages = [
                            url('uploads/images/monthly.png'),
                            url('uploads/images/story.png'),
                            url('uploads/images/tour.png'),
                            url('uploads/images/home.png'),
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

                                <!-- Hover Overlay -->
                                <div class="activity-overlay">
                                    <button type="button"
                                            class="btn btn-primary btn-sm join-us-btn"
                                            data-activity-title="<?= e($activity['title'] ?? '') ?>"
                                            data-activity-date="<?= e(!empty($activity['activity_date']) ? formatDate($activity['activity_date']) : '') ?>">
                                        Join Us
                                    </button>
                                </div>
                            </div>

                            <div class="book-info">
                                <h4 class="book-title"><?= e($activity['title'] ?? '') ?></h4>

                                <p class="book-author">
                                    <?= e($activity['description'] ?? '') ?>
                                </p>

                                <div class="book-meta">
            <span class="chip chip-primary">
                <?= !empty($activity['activity_date']) ? formatDate($activity['activity_date']) : 'Upcoming' ?>
            </span>
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

    <div class="modal-overlay" id="join-us-modal" aria-hidden="true">
        <div class="modal" style="max-width:560px;">
            <div class="modal-header">
                <h3 id="join-us-modal-title">Book a Seat</h3>
                <button type="button" class="btn btn-ghost btn-icon modal-close" id="join-us-modal-close">✕</button>
            </div>
            <form id="join-us-form">
                <?= Csrf::field() ?>
                <input type="hidden" name="activity_title" id="join-us-activity-title">
                <input type="hidden" name="activity_date" id="join-us-activity-date">
                <div class="modal-body" style="display:grid;gap:14px;">
                    <div class="form-group">
                        <label class="form-label" for="join-name">Full Name</label>
                        <input type="text" id="join-name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="join-email">Email</label>
                        <input type="email" id="join-email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="join-phone">Phone</label>
                        <input type="tel" id="join-phone" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="join-seats">Seats to Book</label>
                        <input type="number" id="join-seats" name="seats" class="form-control" min="1" max="20" value="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="join-message">Notes</label>
                        <textarea id="join-message" name="message" class="form-control" rows="3" placeholder="Optional notes or accessibility requirements"></textarea>
                    </div>
                    <p class="text-muted text-sm" id="join-us-activity-summary"></p>
                    <p class="text-muted text-sm" id="join-us-feedback" style="margin:0;"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="join-us-cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="join-us-submit">Book a Seat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.landing-cover-img').forEach((img) => {
        img.addEventListener('error', () => {
            img.remove();
            img.parentElement.querySelector('.landing-cover-fallback')?.style && (img.parentElement.querySelector('.landing-cover-fallback').style.display = 'flex');
        }, { once: true });
    });

    const modal = document.getElementById('join-us-modal');
    const form = document.getElementById('join-us-form');
    const feedback = document.getElementById('join-us-feedback');
    const summary = document.getElementById('join-us-activity-summary');
    const titleInput = document.getElementById('join-us-activity-title');
    const dateInput = document.getElementById('join-us-activity-date');
    const submitBtn = document.getElementById('join-us-submit');
    const base = document.querySelector('meta[name="base-url"]')?.content || '';

    const closeModal = () => {
        modal?.classList.remove('show');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('.join-us-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const activityTitle = btn.dataset.activityTitle || 'Activity';
            const activityDate = btn.dataset.activityDate || '';
            titleInput.value = activityTitle;
            dateInput.value = activityDate;
            summary.textContent = activityDate ? `You are booking a seat for ${activityTitle} (${activityDate}).` : `You are booking a seat for ${activityTitle}.`;
            feedback.textContent = '';
            modal?.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    });

    document.getElementById('join-us-modal-close')?.addEventListener('click', closeModal);
    document.getElementById('join-us-cancel')?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const oldText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Booking...';
        feedback.textContent = 'Submitting your booking...';

        try {
            const response = await fetch(base + '/activities/book-seat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(new FormData(form)).toString(),
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                const errorMessage = data.message || 'Unable to book a seat.';
                feedback.textContent = errorMessage;
                feedback.style.color = 'var(--danger)';
                App.Toast.show(errorMessage, 'error');
                return;
            }

            feedback.textContent = data.message || 'Seat booked successfully.';
            feedback.style.color = 'var(--success)';
            App.Toast.show(data.message || 'Seat booked successfully.', 'success');
            form.reset();
            closeModal();
        } catch (err) {
            feedback.textContent = err.message || 'Booking failed.';
            feedback.style.color = 'var(--danger)';
            App.Toast.show(err.message || 'Booking failed.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = oldText;
        }
    });
});
</script>

<?php View::includeLayout('footer'); ?>
