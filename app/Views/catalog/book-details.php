<?php
$title = $title ?? ($book['title'] ?? 'Book Details');
$reviews = $reviews ?? [];
$similar = $similar ?? [];
View::includeLayout('header', ['title' => $title]);
View::includeLayout('navbar');

$pdfRaw   = $book['pdf_file'] ?? '';
$pdfPath   = !empty($pdfRaw) ? url(ltrim($pdfRaw, '/')) : null;
$coverPath = getBookCover($book);
if (empty($coverPath)) {
    $coverPath = null;
}

$reviewCount = count($reviews);
$averageRating = (float)($book['rating'] ?? 0);
$currentReview = null;
foreach ($reviews as $candidate) {
    if (!empty($candidate['is_owner'])) {
        $currentReview = $candidate;
        break;
    }
}
?>
?>

    <div class="page-background"></div>
    <div class="page-wrapper" style="padding-top: var(--navbar-height);">
        <!-- Floating decorations -->
        <div class="floating-decorations" aria-hidden="true"></div>
        <section class="section">
            <div class="container">
                <!-- Book Detail Hero -->
                <div class="book-detail-hero">
                    <div class="book-detail-cover">
                        <?php if ($coverPath): ?>
                            <img src="<?= e($coverPath) ?>" 
                                 alt="<?= e($book['title']) ?> cover" 
                                 loading="lazy"
                                 data-book-id="<?= $book['id'] ?>"
                                 data-isbn="<?= e($book['isbn'] ?? '') ?>"
                                 onerror="this.style.display='none'; this.parentElement.innerHTML='📖';">
                        <?php else: ?>
                            📖
                        <?php endif; ?>
                    </div>

                    <div class="book-detail-info">
                        <h1><?= e($book['title']) ?></h1>
                        <p class="author-line">by <strong><?= e($book['author']) ?></strong></p>

                        <div class="flex items-center gap-3 mb-4" style="flex-wrap:wrap;">
                            <span id="book-rating-stars"><?= rating_stars($averageRating) ?></span>
                            <span class="text-muted text-sm" id="review-summary">(<span id="book-rating-value"><?= number_format($averageRating, 1) ?></span>/5 · <span id="review-count"><?= $reviewCount ?></span> reviews)</span>
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
                                <div class="meta-label">Price</div>
                                <div class="meta-value"><?php
                                    if (!empty($book['price']) && (float)($book['price'] ?? 0) > 0) {
                                        echo '$' . number_format((float)($book['price'] ?? 0), 2);
                                    } elseif (!isset($book['price']) || (float)($book['price'] ?? 0) === 0.0) {
                                        echo 'Free';
                                    }
                                ?></div>
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

                        <div class="glass-card buy-panel" style="padding:16px;margin-bottom:24px;">
                            <?php if (Auth::check()): ?>
                                <form id="buy-book-form" class="buy-book-form" data-book-id="<?= (int)$book['id'] ?>">
                                    <label class="form-label" for="buy-quantity">Buy Quantity</label>
                                    <div class="flex gap-2" style="align-items:center;flex-wrap:wrap;">
                                        <input
                                            id="buy-quantity"
                                            type="number"
                                            class="form-control"
                                            style="max-width:120px;"
                                            min="1"
                                            max="<?= max(1, (int)($book['available'] ?? 1)) ?>"
                                            value="1"
                                        >
                                        <button type="submit" class="btn btn-primary" id="buy-now-btn">🛒 Buy Now</button>
                                        <span class="text-muted text-sm">In stock: <?= (int)($book['available'] ?? 0) ?></span>
                                    </div>
                                    <p class="text-muted text-sm" id="buy-feedback" style="margin-top:8px;"></p>
                                </form>
                            <?php else: ?>
                                <p class="text-muted">Want to buy this book? Sign in first.</p>
                                <a class="btn btn-primary btn-sm" href="<?= url('login') ?>">Sign In to Buy</a>
                            <?php endif; ?>
                        </div>

                        <div class="book-description">
                            <h3 class="mb-2">About this book</h3>
                            <p><?= e($book['description']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div class="reviews-section">
                    <h3 class="mb-4">Reviews (<span id="reviews-heading-count"><?= $reviewCount ?></span>)</h3>

                    <?php if (Auth::check()): ?>
                        <div class="review-form glass-card mb-4" id="review-form" data-book-id="<?= $book['id'] ?>" data-initial-rating="<?= (int)($currentReview['rating'] ?? 0) ?>">
                            <div class="review-form-header">
                                <h4 class="mb-0" id="review-form-title"><?= $currentReview ? 'Edit Your Review' : 'Write a Review' ?></h4>
                                <button type="button" class="btn btn-ghost btn-sm hidden" id="review-cancel-edit">Cancel</button>
                            </div>
                            <input type="hidden" id="review-id" value="<?= e((string)($currentReview['id'] ?? '')) ?>">
                            <div class="star-rating-input" id="star-input">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <button type="button" class="star-btn" data-rating="<?= $i ?>">☆</button>
                                <?php endfor; ?>
                            </div>
                            <textarea class="form-control mb-2" id="review-comment" aria-label="Review comment" placeholder="Share your thoughts about this book..." rows="3"><?= e($currentReview['comment'] ?? '') ?></textarea>
                            <div class="flex gap-2" style="flex-wrap:wrap;">
                                <button class="btn btn-primary btn-sm" id="submit-review" data-book-id="<?= $book['id'] ?>"><?= $currentReview ? 'Update Review' : 'Submit Review' ?></button>
                                <button type="button" class="btn btn-secondary btn-sm hidden" id="review-reset">Reset</button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="card glass-card">
                        <div class="card-body" id="reviews-card-body">
                            <div id="reviews-list">
                                <?php foreach ($reviews as $r): ?>
                                    <div class="review-card" data-review-id="<?= (int)$r['id'] ?>" data-is-owner="<?= !empty($r['is_owner']) ? '1' : '0' ?>">
                                        <div class="review-header">
                                            <span class="user-avatar" style="width:28px;height:28px;font-size:0.7rem;">
                                                <?= strtoupper(substr($r['user_name'] ?? 'U', 0, 1)) ?>
                                            </span>
                                            <span class="reviewer-name"><?= e($r['user_name'] ?? 'Anonymous') ?></span>
                                            <?= rating_stars((float)$r['rating']) ?>
                                            <span class="review-date"><?= formatDate($r['created_at']) ?></span>
                                        </div>
                                        <p class="review-comment"><?= e($r['comment'] ?? '') ?></p>
                                        <?php if (!empty($r['is_owner']) && Auth::check()): ?>
                                            <div class="review-actions">
                                                <button type="button" class="btn btn-secondary btn-sm review-edit-btn"
                                                        data-review-id="<?= (int)$r['id'] ?>"
                                                        data-rating="<?= (int)$r['rating'] ?>"
                                                        data-comment="<?= e($r['comment'] ?? '') ?>">Edit</button>
                                                <button type="button" class="btn btn-danger btn-sm review-delete-btn"
                                                        data-review-id="<?= (int)$r['id'] ?>">Delete</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center text-muted <?= empty($reviews) ? '' : 'hidden' ?>" id="reviews-empty">
                                <p>No reviews yet. Be the first to review this book!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Similar Books -->
                <div class="similar-books">
                    <h3 class="mb-4">Similar Books</h3>
                    <div class="book-grid">
                        <?php foreach ($similar as $s):
                            if ($s['id'] === $book['id']) continue;

                            $sCoverPath = getBookCover($s);
                            ?>
                            <a href="<?= url('catalog/book/' . $s['id']) ?>" class="book-card glass-card">
                                <div class="book-cover">
                                    <?php if ($sCoverPath): ?>
                                        <img src="<?= e($sCoverPath) ?>" 
                                             alt="<?= e($s['title']) ?> cover" 
                                             loading="lazy"
                                             data-book-id="<?= $s['id'] ?>"
                                             data-isbn="<?= e($s['isbn'] ?? '') ?>"
                                             style="width:100%; height:100%; object-fit:cover;"
                                             onerror="this.style.display='none'; this.parentElement.innerHTML='📖';">
                                    <?php else: ?>
                                        📖
                                    <?php endif; ?>
                                </div>
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
             // Handle buy form submission
              const buyForm = document.getElementById('buy-book-form');
              if (buyForm) {
                 buyForm.addEventListener('submit', (e) => {
                     e.preventDefault();
                     const bookId = buyForm.dataset.bookId;
                      const quantity = parseInt(document.getElementById('buy-quantity')?.value || '1', 10) || 1;

                     // Redirect to payment checkout page
                      window.location.href = '<?= url('payment/checkout') ?>/' + bookId + '?quantity=' + encodeURIComponent(quantity);
                 });
             }

             const reviewForm = document.getElementById('review-form');
            const reviewsBody = document.getElementById('reviews-card-body');
            const reviewsList = document.getElementById('reviews-list');
            const reviewsEmpty = document.getElementById('reviews-empty');
            const reviewCount = document.getElementById('review-count');
            const reviewsHeadingCount = document.getElementById('reviews-heading-count');
            const ratingValue = document.getElementById('book-rating-value');
            const ratingStars = document.getElementById('book-rating-stars');
            const starInput = document.getElementById('star-input');
            const commentInput = document.getElementById('review-comment');
            const submitBtn = document.getElementById('submit-review');
            const reviewIdInput = document.getElementById('review-id');
            const cancelBtn = document.getElementById('review-cancel-edit');
            const resetBtn = document.getElementById('review-reset');
            const formTitle = document.getElementById('review-form-title');
            const base = document.querySelector('meta[name="base-url"]')?.content || '';
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const buyQty = document.getElementById('buy-quantity');
            const buyBtn = document.getElementById('buy-now-btn');
            const buyFeedback = document.getElementById('buy-feedback');

            if (!reviewForm || !starInput || !submitBtn || !commentInput) {
                // Reviews can be hidden for guests; buy flow still needs to initialize.
            }

            if (buyForm && buyQty && buyBtn) {
                buyForm.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const qty = parseInt(buyQty.value, 10) || 0;
                    if (qty < 1) {
                        buyFeedback.textContent = 'Please choose a valid quantity.';
                        buyFeedback.style.color = 'var(--danger)';
                        return;
                    }

                    buyBtn.disabled = true;
                    const oldLabel = buyBtn.textContent;
                    buyBtn.textContent = 'Processing...';
                    buyFeedback.textContent = 'Submitting your order...';
                    buyFeedback.style.color = 'var(--text-muted)';

                    try {
                        const response = await fetch(`${base}/order/buy`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                book_id: buyForm.dataset.bookId,
                                quantity: String(qty),
                                _token: token,
                            }).toString(),
                        });
                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Unable to complete purchase.');
                        }

                        buyFeedback.textContent = data.message || 'Purchase completed.';
                        buyFeedback.style.color = 'var(--success)';
                        App.Toast.show(data.message || 'Purchase completed.', 'success');
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    } catch (error) {
                        buyFeedback.textContent = error.message || 'Purchase failed.';
                        buyFeedback.style.color = 'var(--danger)';
                        App.Toast.show(error.message || 'Purchase failed.', 'error');
                    } finally {
                        buyBtn.disabled = false;
                        buyBtn.textContent = oldLabel;
                    }
                });
            }

            if (!reviewForm || !starInput || !submitBtn || !commentInput) {
                return;
            }

            const renderStars = rating => {
                const value = Number(rating || 0);
                let html = '<span class="stars" aria-label="Rating: ' + value.toFixed(1) + ' out of 5">';
                for (let i = 1; i <= 5; i++) {
                    html += `<i class="star ${i <= Math.round(value) ? 'filled' : 'empty'}">★</i>`;
                }
                html += '</span>';
                return html;
            };

            const escapeHtml = (value) => {
                const div = document.createElement('div');
                div.textContent = value ?? '';
                return div.innerHTML;
            };

            let selectedRating = parseInt(reviewForm.dataset.initialRating || '0', 10) || 0;

            const syncStars = (rating = selectedRating) => {
                starInput.querySelectorAll('.star-btn').forEach((btn, index) => {
                    const active = index < rating;
                    btn.textContent = active ? '★' : '☆';
                    btn.classList.toggle('active', active);
                });
            };

            const setEditorState = (review = null) => {
                if (review) {
                    reviewIdInput.value = review.id;
                    selectedRating = parseInt(review.rating, 10) || 0;
                    commentInput.value = review.comment || '';
                    formTitle.textContent = 'Edit Your Review';
                    submitBtn.textContent = 'Update Review';
                    cancelBtn?.classList.remove('hidden');
                    resetBtn?.classList.remove('hidden');
                } else {
                    reviewIdInput.value = '';
                    selectedRating = 0;
                    commentInput.value = '';
                    formTitle.textContent = 'Write a Review';
                    submitBtn.textContent = 'Submit Review';
                    cancelBtn?.classList.add('hidden');
                    resetBtn?.classList.add('hidden');
                }
                syncStars();
            };

            const updateStats = (stats = {}) => {
                const count = Number(stats.count ?? reviewCount?.textContent ?? 0);
                const average = Number(stats.average ?? ratingValue?.textContent ?? 0);
                if (reviewCount) reviewCount.textContent = String(count);
                if (reviewsHeadingCount) reviewsHeadingCount.textContent = String(count);
                if (ratingValue) ratingValue.textContent = average.toFixed(1);
                if (ratingStars) ratingStars.innerHTML = renderStars(average);
            };

            const renderReviews = (reviews = [], stats = {}) => {
                updateStats(stats);

                if (!reviews.length) {
                    if (reviewsList) reviewsList.innerHTML = '';
                    reviewsEmpty?.classList.remove('hidden');
                    return;
                }

                reviewsEmpty?.classList.add('hidden');

                const cards = reviews.map(review => `
                    <div class="review-card" data-review-id="${review.id}" data-is-owner="${review.is_owner ? '1' : '0'}">
                        <div class="review-header">
                            <span class="user-avatar" style="width:28px;height:28px;font-size:0.7rem;">${escapeHtml((review.user_name || 'U').charAt(0).toUpperCase())}</span>
                            <span class="reviewer-name">${escapeHtml(review.user_name || 'Anonymous')}</span>
                            ${renderStars(review.rating)}
                            <span class="review-date">${escapeHtml(review.created_at ? new Date(review.created_at).toLocaleDateString(undefined, { month: 'short', day: '2-digit', year: 'numeric' }) : '')}</span>
                        </div>
                        <p class="review-comment">${escapeHtml(review.comment || '')}</p>
                        ${review.is_owner ? `
                            <div class="review-actions">
                                <button type="button" class="btn btn-secondary btn-sm review-edit-btn" data-review-id="${review.id}" data-rating="${review.rating}" data-comment="${escapeHtml(review.comment || '')}">Edit</button>
                                <button type="button" class="btn btn-danger btn-sm review-delete-btn" data-review-id="${review.id}">Delete</button>
                            </div>
                        ` : ''}
                    </div>
                `).join('');

                if (reviewsList) reviewsList.innerHTML = cards;
            };

            const handleSubmit = async () => {
                const comment = commentInput.value.trim();
                if (!selectedRating || !comment) {
                    App.Toast.show('Please select a rating and write a comment.', 'warning');
                    return;
                }

                const reviewId = reviewIdInput.value.trim();
                const endpoint = reviewId ? `${base}/api/reviews/${reviewId}/update` : `${base}/api/review`;
                const payload = new URLSearchParams({
                    book_id: reviewForm.dataset.bookId,
                    rating: String(selectedRating),
                    comment,
                    _token: token,
                });

                submitBtn.disabled = true;
                submitBtn.dataset.originalText = submitBtn.dataset.originalText || submitBtn.textContent;
                submitBtn.textContent = reviewId ? 'Updating...' : 'Submitting...';

                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: payload.toString(),
                    });
                    const data = await response.json();

                    if (!response.ok || data.error) {
                        throw new Error(data.error || data.message || 'Unable to save review.');
                    }

                    renderReviews(data.reviews || [], data.stats || {});
                    App.Toast.show(data.message || 'Review saved successfully.', 'success');

                    if (reviewId) {
                        setEditorState(null);
                    } else {
                        commentInput.value = '';
                        selectedRating = 0;
                        syncStars();
                    }
                } catch (error) {
                    App.Toast.show(error.message || 'Failed to submit review.', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = submitBtn.dataset.originalText || 'Submit Review';
                }
            };

            starInput.querySelectorAll('.star-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    selectedRating = parseInt(btn.dataset.rating, 10);
                    syncStars();
                });

                btn.addEventListener('mouseenter', () => {
                    const hoverRating = parseInt(btn.dataset.rating, 10);
                    syncStars(hoverRating);
                });

                btn.addEventListener('mouseleave', () => {
                    syncStars();
                });
            });

            submitBtn.addEventListener('click', handleSubmit);
            cancelBtn?.addEventListener('click', () => setEditorState(null));
            resetBtn?.addEventListener('click', () => setEditorState(null));

            reviewsBody?.addEventListener('click', async (event) => {
                const editBtn = event.target.closest('.review-edit-btn');
                const deleteBtn = event.target.closest('.review-delete-btn');

                if (editBtn) {
                    const review = {
                        id: editBtn.dataset.reviewId,
                        rating: editBtn.dataset.rating,
                        comment: editBtn.dataset.comment,
                    };
                    setEditorState(review);
                    commentInput.focus();
                    return;
                }

                if (deleteBtn) {
                    const reviewId = deleteBtn.dataset.reviewId;
                    if (!window.confirm('Delete this review?')) return;

                    deleteBtn.disabled = true;
                    try {
                        const response = await fetch(`${base}/api/reviews/${reviewId}/delete`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({ _token: token }).toString(),
                        });
                        const data = await response.json();
                        if (!response.ok || data.error) {
                            throw new Error(data.error || data.message || 'Unable to delete review.');
                        }

                        renderReviews(data.reviews || [], data.stats || {});
                        if (reviewIdInput.value === reviewId) {
                            setEditorState(null);
                        }
                        App.Toast.show(data.message || 'Review deleted successfully.', 'success');
                    } catch (error) {
                        App.Toast.show(error.message || 'Failed to delete review.', 'error');
                    } finally {
                        deleteBtn.disabled = false;
                    }
                }
            });

            if (selectedRating > 0) {
                syncStars(selectedRating);
            }

            if (reviewIdInput.value) {
                cancelBtn?.classList.remove('hidden');
                resetBtn?.classList.remove('hidden');
                formTitle.textContent = 'Edit Your Review';
                submitBtn.textContent = 'Update Review';
            }
        });
    </script>

<?php View::includeLayout('footer'); ?>