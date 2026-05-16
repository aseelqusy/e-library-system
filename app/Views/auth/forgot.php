<?php View::includeLayout('header', ['title' => $title]); ?>

<div class="page-wrapper">
    <div class="floating-decorations" aria-hidden="true"></div>

    <div class="auth-page">
        <div class="auth-card glass-card">
            <div class="card-body">
                <div class="auth-header">
                    <div class="brand-icon" aria-hidden="true">🔑</div>
                    <h2>Reset Password</h2>
                    <p>Enter your email and we'll send you a reset link</p>
                </div>

                <form method="POST" action="<?= url('forgot-password') ?>" data-validate>
                    <?= Csrf::field() ?>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-group">
                            <span class="input-icon">✉</span>
                            <input type="email" id="email" name="email" class="form-control"
                                   placeholder="you@example.com"
                                   data-rules="required|email" data-label="Email"
                                   autocomplete="email" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-full btn-lg">Send Reset Link</button>
                </form>

                <div class="auth-footer">
                    Remember your password? <a href="<?= url('login') ?>">Sign in</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php View::includeLayout('footer'); ?>
