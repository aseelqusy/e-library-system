<?php View::includeLayout('header', ['title' => '404 – Page Not Found']); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="page-wrapper" style="display: flex; align-items: center; min-height: calc(100vh - var(--navbar-height));">
    <!-- Floating decorations -->
    <div class="floating-decorations" aria-hidden="true"></div>
    <div class="error-page">
    <div class="error-code">404</div>
    <h1>Page Not Found</h1>
    <p class="text-muted mb-6">The page you're looking for doesn't exist or has been moved.</p>
    <div class="flex gap-3" style="justify-content:center;">
        <a href="<?= url('') ?>" class="btn btn-primary">🏠 Go Home</a>
        <a href="<?= url('catalog') ?>" class="btn btn-secondary">📚 Browse Catalog</a>
    </div>
    <div class="mt-6">
        <p class="text-muted text-sm">Lost? Try the Command Palette — press <kbd style="background:var(--glass-bg);padding:2px 8px;border-radius:4px;border:1px solid var(--glass-border);font-size:12px;">Ctrl + K</kbd></p>
    </div>
    </div>
</div>

<?php View::includeLayout('footer'); ?>
