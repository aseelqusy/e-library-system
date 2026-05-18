<?php View::includeLayout('header', ['title' => $title ?? 'Sign Up']); ?>

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
                    <div style="width:100%; max-width:440px; text-align:center; margin:0;">
                        <a href="<?= url('') ?>" class="text-sm" style="display:block; margin:0;">← Back to home</a>
                    </div>
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
                        <label >
                            <input type="checkbox" name="terms" required> I agree to the <a href="#" data-modal="modal-terms" aria-haspopup="dialog" onclick="event.preventDefault();">Terms of Service</a> and <a href="#" data-modal="modal-privacy" aria-haspopup="dialog" onclick="event.preventDefault();">Privacy Policy</a>
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

<!-- Terms of Service Modal -->
<div id="modal-terms" class="modal-overlay" role="dialog" aria-modal="true" aria-label="Terms of Service">
    <div class="modal" role="document">
        <div class="modal-header">
            <h3>Terms of Service</h3>
            <button class="modal-close btn btn-ghost btn-icon" aria-label="Close">✕</button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom:12px">Welcome to <?= e(APP_NAME) ?>. By creating an account and using our service you agree to the following terms. This is a summary placeholder — replace with your full terms as needed.</p>
            <h4>Use of Service</h4>
            <p class="text-muted">You may use the service for personal, non-commercial purposes. Do not misuse the platform, attempt to access other users' accounts, or upload unlawful content.</p>
            <h4>Content</h4>
            <p class="text-muted">All content provided is for informational purposes only. We reserve the right to remove content that violates our policies.</p>
            <h4>Account</h4>
            <p class="text-muted">You are responsible for keeping your account credentials secure. Contact support if you suspect unauthorized access.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="this.closest('.modal-overlay').classList.remove('show'); document.body.style.overflow='';">Close</button>
        </div>
    </div>
</div>

<!-- Privacy Policy Modal -->
<div id="modal-privacy" class="modal-overlay" role="dialog" aria-modal="true" aria-label="Privacy Policy">
    <div class="modal" role="document">
        <div class="modal-header">
            <h3>Privacy Policy</h3>
            <button class="modal-close btn btn-ghost btn-icon" aria-label="Close">✕</button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom:12px">Your privacy matters. This is a brief placeholder — replace with your full privacy policy.</p>
            <h4>Information We Collect</h4>
            <p class="text-muted">We collect information you provide during registration (name, email) and data related to usage to help improve the service.</p>
            <h4>How We Use Data</h4>
            <p class="text-muted">We use data to operate and improve the platform, communicate with you, and for security purposes. We do not sell personal data.</p>
            <h4>Security</h4>
            <p class="text-muted">We implement reasonable security measures, but cannot guarantee absolute protection. Notify us of suspected breaches.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="this.closest('.modal-overlay').classList.remove('show'); document.body.style.overflow='';">Close</button>
        </div>
    </div>
</div>
