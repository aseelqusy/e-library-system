<?php View::includeLayout('header', ['title' => $title ?? 'My Dashboard']); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-wrapper" style="padding-top: var(--navbar-height);">
    <section class="section">
        <div class="container container-md">
            <div class="card glass-card">
                <div class="card-header">
                    <h2>Welcome back, <?= e(Auth::user()['name'] ?? 'Reader') ?></h2>
                    <p class="text-muted">Quick access to your library activity.</p>
                </div>
                <div class="card-body">
                    <div class="grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                        <a class="btn btn-primary" href="<?= url('user/borrows') ?>">My Borrows</a>
                        <a class="btn btn-secondary" href="<?= url('user/wishlist') ?>">Wishlist</a>
                        <a class="btn btn-ghost" href="<?= url('user/history') ?>">Borrow History</a>
                        <a class="btn btn-ghost" href="<?= url('user/profile') ?>">Profile Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php View::includeLayout('footer'); ?>

