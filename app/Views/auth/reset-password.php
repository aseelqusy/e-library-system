<?php View::includeLayout('header', ['title' => $title ?? 'Reset Password']); ?>

<div class="page-background"></div>
<div class="page-wrapper">
    <div class="floating-decorations" aria-hidden="true"></div>

    <div class="auth-page">
        <div class="auth-card glass-card">
            <div class="card-body">
                <div class="auth-header">
                    <div class="brand-icon" aria-hidden="true">🔐</div>
                    <h2>Create New Password</h2>
                    <p>Enter your new password below</p>
                </div>

                <form method="POST" action="<?= url('reset-password') ?>" data-validate>
                    <?= Csrf::field() ?>
                    <input type="hidden" name="token" value="<?= e($token ?? '') ?>">

                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="password" name="password" class="form-control"
                                   placeholder="Enter new password"
                                   data-rules="required|min:6" data-label="Password"
                                   autocomplete="new-password" required>
                            <button type="button" class="input-action toggle-password" aria-label="Show password">👁</button>
                        </div>
                        <div class="form-hint">At least 6 characters</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                                   placeholder="Confirm new password"
                                   data-rules="required|min:6" data-label="Password Confirmation"
                                   autocomplete="new-password" required>
                            <button type="button" class="input-action toggle-password" aria-label="Show password">👁</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-full btn-lg">Reset Password</button>
                </form>

                <div class="auth-footer">
                    <a href="<?= url('login') ?>">Back to sign in</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php View::includeLayout('footer'); ?>

