<?php View::includeLayout('header', ['title' => $title ?? 'Reset Password']); ?>

<div class="page-background"></div>
<div class="page-wrapper">
    <div class="floating-decorations" aria-hidden="true"></div>

    <div class="auth-page">
        <div class="auth-card glass-card">
            <div class="card-body">
                <div class="auth-header">
                    <div class="brand-icon" aria-hidden="true"><?= !empty($isValid) ? '🔑' : '⚠️' ?></div>
                    <h2><?= !empty($isValid) ? 'Create New Password' : 'Reset Link Unavailable' ?></h2>
                    <p>
                        <?= !empty($isValid)
                            ? 'Choose a strong password to secure your account'
                            : 'The reset link is invalid or expired' ?>
                    </p>
                </div>

                <?php if (empty($isValid)): ?>
                    <div class="glass-card" style="padding:20px;border:1px solid rgba(239,68,68,.3);background:rgba(239,68,68,.08);margin-bottom:20px;">
                        <div style="display:flex;gap:14px;align-items:flex-start;">
                            <div style="font-size:1.8rem;line-height:1;">❌</div>
                            <div>
                                <h3 style="margin:0 0 8px;">Invalid or expired link</h3>
                                <p style="margin:0;color:var(--text-muted);">
                                    <?= e($errorMessage ?? 'Please request a new password reset link.') ?>
                                </p>
                            </div>
                        </div>
                        <div style="margin-top:16px;">
                            <a href="<?= url('forgot-password') ?>" class="btn btn-primary btn-sm">Request a new link</a>
                            <a href="<?= url('login') ?>" class="btn btn-secondary btn-sm" style="margin-left:8px;">Back to sign in</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="glass-card" style="padding:20px;border:1px solid rgba(59,130,246,.28);background:rgba(59,130,246,.08);margin-bottom:20px;">
                        <div style="display:flex;gap:14px;align-items:flex-start;">
                            <div style="font-size:1.8rem;line-height:1;">🛡️</div>
                            <div>
                                <h3 style="margin:0 0 8px;">Link verified for <?= e($userName ?? 'your account') ?></h3>
                                <p style="margin:0;color:var(--text-muted);">
                                    This link is valid for 30 minutes. Enter a new password below to complete the reset.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="<?= url('update-password') ?>" data-validate>
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
                <?php endif; ?>

                <div class="auth-footer">
                    <a href="<?= url('login') ?>">Back to sign in</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php View::includeLayout('footer'); ?>

