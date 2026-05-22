    <footer class="site-footer" role="contentinfo">
        <div class="container site-footer-inner">
            <section class="site-footer-col site-footer-brand-col">
                <img class="site-footer-logo" src="<?= url('uploads/images/logo.png') ?>" alt="<?= e(APP_NAME) ?> logo">
                <div class="site-footer-brand"><?= e(APP_NAME) ?></div>
                <p class="site-footer-copy">A modern platform for discovering, borrowing, and purchasing books.</p>
                <p class="site-footer-small">Open knowledge. Accessible reading. Built for every screen.</p>
            </section>

            <section class="site-footer-col">
                <h4>Explore</h4>
                <div class="site-footer-links">
                    <a href="<?= url('catalog') ?>">Catalog</a>
                    <a href="<?= url('catalog/categories') ?>">Categories</a>
                    <a href="<?= url('about') ?>">About Us</a>
                    <a href="<?= url('contact') ?>">Contact</a>
                </div>
            </section>

            <section class="site-footer-col">
                <h4>Account</h4>
                <div class="site-footer-links">
                    <?php if (Auth::check()): ?>
                        <a href="<?= url('dashboard') ?>">Dashboard</a>
                        <a href="<?= url('user/orders') ?>">My Orders</a>
                        <a href="<?= url('user/profile') ?>">Profile</a>
                    <?php else: ?>
                        <a href="<?= url('login') ?>">Sign In</a>
                        <a href="<?= url('register') ?>">Create Account</a>
                    <?php endif; ?>
                </div>
            </section>

            <section class="site-footer-col">
                <h4>Contact</h4>
                <p class="site-footer-small">support@luminara-library.com</p>
                <p class="site-footer-small">Knowledge District</p>
                <div class="site-footer-small">
                <div class="site-footer-small">
                    <span>© <?= date('Y') ?> <?= e(APP_NAME) ?></span>
                </div>
            </section>
        </div>
    </footer>

    <script src="<?= asset('js/theme.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script src="<?= asset('js/animations.js') ?>"></script>
    <script src="<?= asset('js/music-player.js') ?>"></script>
    <script src="<?= asset('js/form-validation.js') ?>"></script>
    <script src="<?= asset('js/book-covers.js') ?>"></script>
</body>
</html>
