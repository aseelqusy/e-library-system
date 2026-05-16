<?php View::includeLayout('header', ['title' => $title]); ?>

<div class="page-wrapper">
    <div class="floating-decorations" aria-hidden="true"></div>

    <div class="auth-page">
        <div class="auth-card glass-card">
            <div class="card-body">
                <div class="auth-header">
                    <div class="brand-icon" aria-hidden="true">✨</div>
                    <h2>Create Account</h2>
                    <p>Join <?= e(APP_NAME) ?> and start your reading journey</p>
                </div>

                <form method="POST" action="<?= url('register') ?>" data-validate>
                    <?= Csrf::field() ?>

                    <div class="form-group">
                        <label class="form-label" for="name">Full Name</label>
                        <div class="input-group">
                            <span class="input-icon">👤</span>
                            <input type="text" id="name" name="name" class="form-control"
                                   placeholder="Your full name"
                                   data-rules="required|min:2" data-label="Name"
                                   autocomplete="name" required>
                        </div>
                    </div>

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

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="password" name="password" class="form-control"
                                   placeholder="Minimum 6 characters"
                                   data-rules="required|min:6" data-label="Password"
                                   autocomplete="new-password" required>
                            <button type="button" class="input-action toggle-password" aria-label="Show password">👁</button>
                        </div>
                        <div class="password-strength">
                            <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
                        </div>
                        <div class="strength-text"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                                   placeholder="Repeat your password"
                                   data-rules="required|match:password" data-label="Confirm Password"
                                   autocomplete="new-password" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="terms" required>
                            I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-full btn-lg">Create Account</button>
                </form>

                <div class="auth-footer">
                    Already have an account? <a href="<?= url('login') ?>">Sign in</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php View::includeLayout('footer'); ?>
