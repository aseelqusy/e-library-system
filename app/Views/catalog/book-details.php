<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<?php
$coverRaw = $book['cover_image'] ?? $book['cover'] ?? '';
$pdfRaw   = $book['pdf_file'] ?? '';
$coverPath = !empty($coverRaw) ? url(ltrim($coverRaw, '/')) : null;
$pdfPath   = !empty($pdfRaw)   ? url(ltrim($pdfRaw,   '/')) : null;
?>

    <div class="page-wrapper" style="padding-top: var(--navbar-height);">
        <section class="section">
            <div class="container">
                <!-- Book Detail Hero -->
                <div class="book-detail-hero">
                    <div class="book-detail-cover">
                        <?php if ($coverPath): ?>
                            <img src="<?= $coverPath ?>" alt="<?= e($book['title']) ?> cover" loading="lazy">
                        <?php else: ?>
                            📖
                        <?php endif; ?>
                    </div>

                    <div class="book-detail-info">
                        <h1><?= e($book['title']) ?></h1>
                        <p class="author-line">by <strong><?= e($book['author']) ?></strong></p>

                        <div class="flex items-center gap-3 mb-4" style="flex-wrap:wrap;">
                            <?= rating_stars($book['rating']) ?>
                            <span class="text-muted text-sm">(<?= $book['rating'] ?>/5 · <?= count($reviews) ?> reviews)</span>
                            <span class="chip chip-primary"><?= e($category['name'] ?? 'General') ?></span>
                            <?= status_chip($book['available'] > 0 ? 'available' : 'borrowed') ?>
                        </div>

                        <div class="book-detail-meta">
                            <div class="meta-item glass-card">
                                <div class="meta-label">Pages</div>
                                <div class="meta-value"><?= $book['pages'] ?></div>
                            </div>
                            <div class="meta-item glass-card">
                                <div class="meta-label">Year</div>
                                <div class="meta-value"><?= $book['year'] > 0 ? $book['year'] : abs($book['year']) . ' BC' ?></div>
                            </div>
                            <div class="meta-item glass-card">
                                <div class="meta-label">Copies</div>
                                <div class="meta-value"><?= $book['available'] ?>/<?= $book['copies'] ?></div>
                            </div>
                            <div class="meta-item glass-card">
                                <div class="meta-label">ISBN</div>
                                <div class="meta-value" style="font-size:0.7rem;"><?= e($book['isbn']) ?></div>
                            </div>
                        </div>

                        <div class="book-detail-actions">
                            <?php if ($book['available'] > 0): ?>
                                <button class="btn btn-primary btn-lg" data-borrow-action="borrow" data-book-id="<?= $book['id'] ?>">
                                    📖 Borrow Now
                                </button>
                            <?php else: ?>
                                <button class="btn btn-outline btn-lg" data-borrow-action="reserve" data-book-id="<?= $book['id'] ?>">
                                    🔔 Reserve
                                </button>
                            <?php endif; ?>
                            <?php if ($pdfPath): ?>
                                <a class="btn btn-secondary btn-lg" href="<?= $pdfPath ?>" target="_blank" rel="noopener">
                                    📄 Open PDF
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-lg" disabled title="PDF not available">
                                    📄 PDF Unavailable
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-secondary btn-lg wishlist-btn" data-book-id="<?= $book['id'] ?>">
                                ❤ Wishlist
                            </button>
                        </div>

                        <div class="book-description">
                            <h3 class="mb-2">About this book</h3>
                            <p><?= e($book['description']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div class="reviews-section">
                    <h3 class="mb-4">Reviews (<?= count($reviews) ?>)</h3>

                    <?php if (Auth::check()): ?>
                        <div class="review-form glass-card mb-4" id="review-form">
                            <h4 class="mb-2">Write a Review</h4>
                            <div class="star-rating-input" id="star-input">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <button class="star-btn" data-rating="<?= $i ?>">☆</button>
                                <?php endfor; ?>
                            </div>
                            <textarea class="form-control mb-2" id="review-comment" placeholder="Share your thoughts about this book..." rows="3"></textarea>
                            <button class="btn btn-primary btn-sm" id="submit-review" data-book-id="<?= $book['id'] ?>">Submit Review</button>
                        </div>
                    <?php endif; ?>

                    <div class="card glass-card">
                        <?php if (empty($reviews)): ?>
                            <div class="card-body text-center text-muted">
                                <p>No reviews yet. Be the first to review this book!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($reviews as $r): ?>
                                <?php
                                require_once APP_PATH . '/Models/User.php';
                                $reviewer = User::find($r['user_id']);
                                ?>
                                <div class="review-card">
                                    <div class="review-header">
                                    <span class="user-avatar" style="width:28px;height:28px;font-size:0.7rem;">
                                        <?= strtoupper(substr($reviewer['name'] ?? 'U', 0, 1)) ?>
                                    </span>
                                        <span class="reviewer-name"><?= e($reviewer['name'] ?? 'Anonymous') ?></span>
                                        <?= rating_stars($r['rating']) ?>
                                        <span class="review-date"><?= formatDate($r['created_at']) ?></span>
                                    </div>
                                    <p class="review-comment"><?= e($r['comment']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Similar Books -->
                <div class="similar-books">
                    <h3 class="mb-4">Similar Books</h3>
                    <div class="book-grid">
                        <?php foreach ($similar as $s):
                            if ($s['id'] === $book['id']) continue;
                            ?>
                            <a href="<?= url('books/' . $s['id']) ?>" class="book-card glass-card">
                                <div class="book-cover">📖</div>
                                <div class="book-info">
                                    <h4 class="book-title"><?= e($s['title']) ?></h4>
                                    <p class="book-author"><?= e($s['author']) ?></p>
                                    <div class="book-meta">
                                        <span class="book-rating">★ <?= $s['rating'] ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Star rating input
            const starInput = document.getElementById('star-input');
            let selectedRating = 0;
            if (starInput) {
                starInput.querySelectorAll('.star-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        selectedRating = parseInt(btn.dataset.rating);
                        starInput.querySelectorAll('.star-btn').forEach((b, i) => {
                            b.textContent = i < selectedRating ? '★' : '☆';
                            b.classList.toggle('active', i < selectedRating);
                        });
                    });
                    btn.addEventListener('mouseenter', () => {
                        const r = parseInt(btn.dataset.rating);
                        starInput.querySelectorAll('.star-btn').forEach((b, i) => {
                            b.textContent = i < r ? '★' : '☆';
                        });
                    });
                    btn.addEventListener('mouseleave', () => {
                        starInput.querySelectorAll('.star-btn').forEach((b, i) => {
                            b.textContent = i < selectedRating ? '★' : '☆';
                        });
                    });
                });
            }

            // Submit review
            const submitBtn = document.getElementById('submit-review');
            if (submitBtn) {
                submitBtn.addEventListener('click', () => {
                    const comment = document.getElementById('review-comment').value.trim();
                    if (!selectedRating || !comment) {
                        App.Toast.show('Please select a rating and write a comment.', 'warning');
                        return;
                    }
                    const base = document.querySelector('meta[name="base-url"]')?.content || '';
                    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

                    fetch(base + '/api/review', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `book_id=${submitBtn.dataset.bookId}&rating=${selectedRating}&comment=${encodeURIComponent(comment)}&_token=${encodeURIComponent(token)}`
                    })
                        .then(r => r.json())
                        .then(data => {
                            App.Toast.show(data.message || 'Review submitted!', 'success');
                            document.getElementById('review-comment').value = '';
                            selectedRating = 0;
                            starInput.querySelectorAll('.star-btn').forEach(b => { b.textContent = '☆'; b.classList.remove('active'); });
                        })
                        .catch(() => App.Toast.show('Failed to submit review.', 'error'));
                });
            }
        });
    </script>

<?php View::includeLayout('footer'); ?>