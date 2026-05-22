<?php View::includeLayout('header', ['title' => '403 – Access Denied']); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="page-wrapper" style="display: flex; align-items: center; min-height: calc(100vh - var(--navbar-height));">
    <!-- Floating decorations -->
    <div class="floating-decorations" aria-hidden="true"></div>
    <div class="error-page">
        <div class="error-code">403</div>
        <h1>Access Denied</h1>
        <p class="text-muted mb-6">You don't have permission to access this page.</p>
        <div class="flex gap-3" style="justify-content:center;">
            <a href="<?= url('') ?>" class="btn btn-primary">🏠 Go Home</a>
            <?php if (!Auth::check()): ?>
                <a href="<?= url('login') ?>" class="btn btn-secondary">🔑 Sign In</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php View::includeLayout('footer'); ?>
