<?php
$title = $title ?? 'My Orders';
$orders = is_array($orders ?? null) ? $orders : [];
View::includeLayout('header', ['title' => $title]);
?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="page-wrapper user-dashboard-page" style="padding-top: var(--navbar-height);">
    <!-- Floating decorations -->
    <div class="floating-decorations" aria-hidden="true"></div>
    <section class="section dashboard-section">
        <div class="container">
            <div class="section-header dashboard-page-header">
                <div>
                    <h2 class="text-gradient">My Orders</h2>
                    <p class="text-muted text-sm">See your purchase history and order status.</p>
                </div>
                <a href="<?= url('catalog') ?>" class="btn btn-primary btn-sm">Buy More Books</a>
            </div>

            <div class="dashboard-container">
                <?php View::partial('user.partials.sidebar', ['current_page' => $current_page ?? 'orders']); ?>

                <main class="dashboard-main">
                    <?php if (empty($orders)): ?>
                        <div class="text-center" style="padding:60px 0;">
                            <p style="font-size:3rem;margin-bottom:12px;">🧾</p>
                            <h3>No orders yet</h3>
                            <p class="text-muted">You can buy books directly from the catalog.</p>
                            <a href="<?= url('catalog') ?>" class="btn btn-primary mt-4">Explore Books</a>
                        </div>
                    <?php else: ?>
                        <div class="book-grid my-orders-grid">
                            <?php foreach ($orders as $order): ?>
                                <?php
                                    $orderBook = [
                                        'cover_image' => $order['book_cover'] ?? null,
                                        'isbn' => $order['isbn'] ?? null,
                                    ];
                                    $cover = getBookCover($orderBook);
                                ?>
                                <article class="glass-card order-card">
                                    <div class="order-card-cover">
                                        <?php if ($cover): ?>
                                            <img src="<?= e($cover) ?>"
                                                 alt="<?= e($order['book_title'] ?? 'Book') ?> cover"
                                                 loading="lazy"
                                                 data-isbn="<?= e($order['isbn'] ?? '') ?>"
                                                 onerror="this.style.display='none'; this.parentElement.innerHTML='📖';">
                                        <?php else: ?>
                                            📖
                                        <?php endif; ?>
                                    </div>
                                    <div class="order-card-body">
                                        <h4><?= e($order['book_title'] ?? 'Unknown Book') ?></h4>
                                        <p class="text-muted text-sm"><?= e($order['book_author'] ?? '') ?></p>
                                        <div class="order-card-meta">
                                            <span>Qty: <strong><?= (int)($order['quantity'] ?? 0) ?></strong></span>
                                            <span>Price: <strong>$<?= number_format((float)($order['total_price'] ?? 0), 2) ?></strong></span>
                                        </div>
                                        <div class="order-card-meta">
                                            <span><?= status_chip((string)($order['status'] ?? 'pending')) ?></span>
                                            <span class="text-muted text-sm"><?= !empty($order['created_at']) ? formatDate($order['created_at']) : 'N/A' ?></span>
                                        </div>
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

