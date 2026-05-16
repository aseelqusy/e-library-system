<?php $title = 'My Profile'; ?>
<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-wrapper" style="padding-top: var(--navbar-height);">
    <section class="section">
        <div class="container container-md">
            <!-- Profile Header -->
            <div class="profile-header glass-card">
                <div class="user-avatar user-avatar-lg">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="profile-info">
                    <h2><?= e($user['name'] ?? '') ?></h2>
                    <p class="profile-email"><?= e($user['email'] ?? '') ?></p>
                    <span class="chip chip-primary profile-role"><?= ucfirst(e($user['role'] ?? 'member')) ?></span>
                </div>
            </div>

            <div class="tab-container">
                <div class="tabs">
                    <button class="tab-btn active" data-tab="tab-details">Profile Details</button>
                    <button class="tab-btn" data-tab="tab-security">Security</button>
                </div>

                <!-- Details Tab -->
                <div class="tab-pane active" id="tab-details">
                    <div class="card glass-card">
                        <div class="card-body">
                            <form method="POST" action="<?= url('user/profile') ?>" data-validate>
                                <?= Csrf::field() ?>

                                <div class="form-group">
                                    <label class="form-label" for="name">Full Name</label>
                                    <input type="text" id="name" name="name" class="form-control"
                                           value="<?= e($user['name']) ?>"
                                           data-rules="required|min:2" data-label="Name">
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" id="email" name="email" class="form-control"
                                           value="<?= e($user['email']) ?>"
                                           data-rules="required|email" data-label="Email">
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="phone">Phone</label>
                                    <input type="text" id="phone" name="phone" class="form-control"
                                           value="<?= e($user['phone'] ?? '') ?>"
                                           placeholder="Your phone number">
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="bio">Bio</label>
                                    <textarea id="bio" name="bio" class="form-control"
                                              placeholder="Tell us about yourself..."><?= e($user['bio'] ?? '') ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div class="tab-pane" id="tab-security">
                    <div class="card glass-card">
                        <div class="card-body">
                            <h3 class="mb-4">Change Password</h3>
                            <form data-validate>
                                <div class="form-group">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" placeholder="Enter current password" data-rules="required">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">New Password</label>
                                    <input type="password" id="new_password" class="form-control" placeholder="Minimum 6 characters" data-rules="required|min:6">
                                    <div class="password-strength">
                                        <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" placeholder="Repeat new password" data-rules="required|match:new_password">
                                </div>
                                <button type="button" class="btn btn-primary" onclick="App.Toast.show('Password change will work after DB integration.','info')">Update Password</button>
                            </form>

                            <hr style="border-color:var(--border);margin:32px 0;">

                            <h3 class="mb-4">Account</h3>
                            <p class="text-muted text-sm mb-4">Member since: <?= !empty($user['created_at']) ? formatDate($user['created_at']) : 'N/A' ?></p>
                            <button class="btn btn-danger btn-sm" onclick="App.Toast.show('Account deletion will be available after DB integration.','warning')">Delete Account</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php View::includeLayout('footer'); ?>
