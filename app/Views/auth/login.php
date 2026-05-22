<?php View::includeLayout('header', ['title' => $title ?? 'Sign In']); ?>

<div class="page-background"></div>
<div class="page-wrapper">
    <div class="floating-decorations" aria-hidden="true"></div>

    <div class="auth-page">
        <div class="auth-card glass-card">
            <div class="card-body">
                <div class="auth-header">
                    <div class="brand-icon" aria-hidden="true">📚</div>
                    <h2>Sign In</h2>
                    <p>Access your <?= e(APP_NAME) ?> account</p>
                </div>

                <form method="POST" action="<?= url('login') ?>" data-validate>
                    <?= Csrf::field() ?>
                    <div class="auth-back-link">
                        <a href="<?= url('') ?>" class="text-sm">← Back to home</a>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <div class="input-group">
                            <span class="input-icon">✉</span>
                            <input type="email" id="email" name="email" class="form-control"
                                   placeholder="you@example.com"
                                   data-rules="required|email" data-label="Email"
                                   autocomplete="email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="password" name="password" class="form-control"
                                   placeholder="Enter your password"
                                   data-rules="required" data-label="Password"
                                   autocomplete="current-password" required>
                            <button type="button" class="input-action toggle-password" aria-label="Show password">👁</button>
                        </div>
                    </div>

                    <div class="form-row-between">
                        <label class="form-check">
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        <a href="<?= url('forgot-password') ?>" class="text-sm">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary w-full btn-lg">Sign In</button>

                    <div class="auth-divider">or continue with</div>

                    <div class="flex gap-2 auth-social-grid">
                        <button type="button" class="btn btn-secondary w-full" onclick="App.Toast.show('Social login coming soon!','info')">🔵 Google</button>
                    </div>
                </form>

                <div class="auth-footer">
                    Don't have an account? <a href="<?= url('register') ?>">Create one</a>
                </div>

                <div class="auth-footer mt-2 text-xs text-muted">

                </div>
            </div>
        </div>
    </div>
</div>

<?php View::includeLayout('footer'); ?>
