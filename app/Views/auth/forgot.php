<?php View::includeLayout('header', ['title' => $title ?? 'Reset Password']); ?>

<div class="page-background"></div>
<div class="page-wrapper">
    <div class="floating-decorations" aria-hidden="true"></div>

    <div class="auth-page">
        <div class="auth-card glass-card">
            <div class="card-body">
                <div class="auth-header">
                    <div class="brand-icon" aria-hidden="true">🔐</div>
                    <h2>Reset Password</h2>
                    <p>Enter your email and we’ll send you reset instructions</p>
                </div>

                <?php if (!empty($resetSent)): ?>
                    <div class="glass-card" style="margin-bottom:20px;padding:20px;border:1px solid rgba(16,185,129,.28);background:rgba(16,185,129,.08);">
                        <div style="display:flex;gap:14px;align-items:flex-start;">
                            <div style="font-size:1.8rem;line-height:1;">✅</div>
                            <div>
                                <h3 style="margin:0 0 8px;">Reset link simulated successfully</h3>
                                <p style="margin:0 0 10px;color:var(--text-muted);">
                                    <?= e($resetNotice ?? 'If the email exists, the reset link has been simulated and saved to mail_dump.txt.') ?>
                                </p>
                                <div style="font-size:.9rem;color:var(--text-muted);">
                                    <strong>Dump file:</strong> <code><?= e($dumpFile ?? 'mail_dump.txt') ?></code>
                                </div>
                                <?php if (!empty($simulatedLink)): ?>
                                    <div style="margin-top:12px;">
                                        <div style="font-size:.85rem;color:var(--text-muted);margin-bottom:6px;">Simulated reset link:</div>
                                        <code style="display:block;word-break:break-all;padding:10px 12px;border-radius:10px;background:rgba(15,23,42,.55);border:1px solid rgba(148,163,184,.18);">
                                            <?= e($simulatedLink) ?>
                                        </code>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= url('forgot-password') ?>" data-validate>
                    <?= Csrf::field() ?>
                    <div class="auth-back-link">
                        <a href="<?= url('login') ?>" class="text-sm">← Back to sign in</a>
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
                        <div class="form-hint">We’ll email you a secure reset link if your account exists.</div>
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
