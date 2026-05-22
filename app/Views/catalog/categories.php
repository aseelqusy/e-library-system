<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="page-wrapper" style="padding-top: var(--navbar-height);">
    <!-- Floating decorations -->
    <div class="floating-decorations" aria-hidden="true"></div>

    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="text-gradient">Categories</h2>
            </div>

            <div class="category-grid">
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= url('catalog?category=' . $cat['id']) ?>" class="category-card glass-card">
                        <div class="cat-icon"><?= $cat['icon'] ?></div>
                        <div class="cat-name"><?= e($cat['name']) ?></div>
                        <p class="text-sm text-muted mt-2"><?= e($cat['description']) ?></p>
                        <div class="cat-count mt-2"><?= $counts[$cat['id']] ?? 0 ?> books</div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<?php View::includeLayout('footer'); ?>
