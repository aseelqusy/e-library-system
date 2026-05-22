<?php
$title = $title ?? 'My Reviews';
$reviews = is_array($reviews ?? null) ? $reviews : [];
$reviewCount = (int)($reviewCount ?? count($reviews));
$averageRating = (float)($averageRating ?? 0);

View::includeLayout('header', ['title' => $title]);
View::includeLayout('navbar');
?>

<div class="page-background"></div>
<div class="page-wrapper user-dashboard-page" style="padding-top: var(--navbar-height);">
    <!-- Floating decorations -->
    <div class="floating-decorations" aria-hidden="true"></div>
    <section class="section dashboard-section">
        <div class="container">
            <div class="section-header dashboard-page-header">
                <div>
                    <h2 class="text-gradient">My Reviews</h2>
                    <p class="text-muted text-sm">All the reviews you have posted across the library.</p>
                </div>
                <div class="flex gap-2" style="flex-wrap:wrap;">
                    <span class="chip chip-primary"><?= $reviewCount ?> reviews</span>
                    <span class="chip chip-outline">Avg. <?= number_format($averageRating, 1) ?>/5</span>
                </div>
            </div>

            <div class="dashboard-container">
                <?php View::partial('user.partials.sidebar', ['current_page' => $current_page ?? 'reviews']); ?>

                <main class="dashboard-main">
                    <?php if (empty($reviews)): ?>
                        <div class="glass-card" style="padding:56px 24px;text-align:center;">
                            <p style="font-size:3rem;margin-bottom:12px;">💬</p>
                            <h3>No reviews yet</h3>
                            <p class="text-muted">Once you review a book, it will appear here.</p>
                            <a href="<?= url('catalog') ?>" class="btn btn-primary mt-4">Browse Books</a>
                        </div>
                    <?php else: ?>
                        <div class="book-grid my-reviews-grid">
                            <?php foreach ($reviews as $review): ?>
                                <article class="glass-card review-card-panel">
                                    <div class="review-card-panel-header">
                                        <div>
                                            <p class="text-muted text-sm mb-1"><?= !empty($review['created_at']) ? formatDate($review['created_at']) : 'Recently' ?></p>
                                            <h4 class="mb-1"><?= e($review['book_title'] ?? 'Unknown Book') ?></h4>
                                        </div>
                                        <div class="review-card-rating">
                                            <?= rating_stars((float)($review['rating'] ?? 0)) ?>
                                        </div>
                                    </div>

                                    <p class="review-card-comment"><?= e($review['comment'] ?? '') ?></p>

                                    <div class="review-card-actions">
                                        <a href="<?= url('books/' . (int)($review['book_id'] ?? 0)) ?>" class="btn btn-secondary btn-sm">View Book</a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </section>
</div>

<?php View::includeLayout('footer'); ?>

