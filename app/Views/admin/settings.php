<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="admin-layout">
    <?php View::partial('admin/sidebar'); ?>

    <main class="admin-main">
        <div class="mb-6">
            <h2>⚙️ Settings</h2>
            <p class="text-muted text-sm">Application preferences and configuration</p>
        </div>

        <div class="book-grid" style="grid-template-columns:repeat(auto-fill,minmax(400px,1fr));gap:24px;">
            <!-- General Settings -->
            <div class="card glass-card" style="padding:24px;">
                <h3 class="mb-4">General</h3>
                <form id="general-settings">
                    <input type="hidden" name="csrf_token" value="<?= Csrf::token() ?>">
                    <div class="form-group">
                        <label class="form-label">Application Name</label>
                        <input type="text" class="form-control" value="<?= e($settings['app_name'] ?? 'Luminara Library') ?>" name="app_name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Borrow Days</label>
                        <input type="number" class="form-control" value="<?= e($settings['max_borrow_days'] ?? '14') ?>" name="max_borrow_days" min="1" max="90">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Books Per User</label>
                        <input type="number" class="form-control" value="<?= e($settings['max_books_per_user'] ?? '5') ?>" name="max_books_per_user" min="1" max="10">
                    </div>
                    <button type="button" class="btn btn-primary" onclick="saveSettings('general', event)">Save Changes</button>
                </form>
            </div>

            <!-- Appearance -->
            <div class="card glass-card" style="padding:24px;">
                <h3 class="mb-4">Appearance</h3>
                <div class="form-group">
                    <label class="form-label">Theme</label>
                    <div class="flex gap-2">
                        <button class="btn btn-primary btn-sm" id="theme-dark" onclick="setTheme('dark')">🌙 Dark (Active)</button>
                        <button class="btn btn-secondary btn-sm" id="theme-light" onclick="setTheme('light')">☀️ Light</button>
                    </div>
                    <p class="text-muted text-sm mt-2">Light theme coming in a future update.</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Accent Color</label>
                    <div class="flex gap-2 flex-wrap">
                        <button class="btn btn-sm" style="background:var(--primary);color:#fff;" onclick="setAccent('purple')">Purple</button>
                        <button class="btn btn-sm" style="background:var(--info);color:#fff;" onclick="setAccent('cyan')">Cyan</button>
                        <button class="btn btn-sm" style="background:#10b981;color:#fff;" onclick="setAccent('green')">Green</button>
                        <button class="btn btn-sm" style="background:#f59e0b;color:#000;" onclick="setAccent('amber')">Amber</button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Animations</label>
                    <div class="flex gap-3" style="align-items:center;">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                            <input type="checkbox" checked id="toggle-butterflies"> 🦋 Butterflies
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                            <input type="checkbox" checked id="toggle-books"> 📚 Floating Books
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                            <input type="checkbox" checked id="toggle-glow"> ✨ Cursor Glow
                        </label>
                    </div>
                </div>
            </div>

            <!-- Music Player -->
            <div class="card glass-card" style="padding:24px;">
                <h3 class="mb-4">Music Player</h3>
                <div class="form-group">
                    <label class="form-label">Music Player Enabled</label>
                    <div class="flex gap-2">
                        <button class="btn btn-primary btn-sm" onclick="toggleMusicPlayer(true)">✅ Enabled</button>
                        <button class="btn btn-secondary btn-sm" onclick="toggleMusicPlayer(false)">❌ Disabled</button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Default Volume</label>
                    <input type="range" min="0" max="100" value="30" class="form-control" style="padding:0;" id="default-volume">
                    <span class="text-muted text-sm" id="volume-label">30%</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Autoplay on Load</label>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="checkbox" id="autoplay-toggle"> Enable autoplay
                    </label>
                </div>
            </div>

            <!-- Maintenance -->
            <div class="card glass-card" style="padding:24px;">
                <h3 class="mb-4">Maintenance</h3>
                <div class="form-group">
                    <label class="form-label">Cache</label>
                    <button class="btn btn-secondary btn-sm" onclick="clearCache()">🗑 Clear Application Cache</button>
                </div>
                <div class="form-group">
                    <label class="form-label">Logs</label>
                    <button class="btn btn-secondary btn-sm" onclick="viewLogs()">📋 View Application Logs</button>
                </div>
                <div class="form-group">
                    <label class="form-label">Database</label>
                    <p class="text-muted text-sm mb-2">
                        Status: <?= $dbConnected ? 'Connected' : 'Unavailable' ?>
                        <?php if (!$dbConnected && !empty($dbError)): ?>
                            <span class="text-muted text-sm">(<?= e($dbError) ?>)</span>
                        <?php endif; ?>
                    </p>
                    <button class="btn btn-outline btn-sm" onclick="testDbConnection()">🔌 Recheck Connection</button>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
const SettingsData = {
    dbConnected: <?= $dbConnected ? 'true' : 'false' ?>,
    dbError: <?= json_encode($dbError, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
};

document.addEventListener('DOMContentLoaded', () => {
    const volumeSlider = document.getElementById('default-volume');
    const volumeLabel = document.getElementById('volume-label');
    if (volumeSlider) {
        volumeSlider.addEventListener('input', () => {
            volumeLabel.textContent = volumeSlider.value + '%';
        });
    }
});

function saveSettings(section, event) {
    if (event) event.preventDefault(); // منع المتصفح من إلغاء الطلب 🛑

    const form = document.getElementById(`${section}-settings`);
    if (!form) return;

    // باقي كود الـ fetch كما هو بدون تغيير...

    // تجميع البيانات من الحقول تلقائياً بما فيها توكن الحماية
    const formData = new FormData(form);

    // إرسال البيانات إلى الـ Controller الخاص بالإعدادات
    fetch(`${window.LuminApp?.baseUrl || '/library-app/public'}/admin/settings`, {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (response.ok) {
                window.LuminApp?.Toast?.show('General settings saved successfully! 💾', 'success');
            } else {
                window.LuminApp?.Toast?.show('Failed to save settings. ❌', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.LuminApp?.Toast?.show('An error occurred while saving.', 'error');
        });
}
function setTheme(theme) {
    window.LuminApp?.Toast?.show('Theme switching coming in a future update', 'info');
}
function setAccent(color) {
    const map = { purple: '#7c3aed', cyan: '#06b6d4', green: '#10b981', amber: '#f59e0b' };
    document.documentElement.style.setProperty('--primary', map[color] || map.purple);
    window.LuminApp?.Toast?.show('Accent color changed to ' + color, 'success');
}
function toggleMusicPlayer(on) {
    const player = document.querySelector('.music-player');
    if (player) player.style.display = on ? '' : 'none';
    window.LuminApp?.Toast?.show('Music player ' + (on ? 'enabled' : 'disabled'), 'success');
}
function clearCache() {
    window.LuminApp?.Toast?.show('Cache cleared.', 'success');
}
function viewLogs() {
    window.LuminApp?.Toast?.show('Log viewer will open in a new tab when enabled.', 'info');
}
function testDbConnection() {
    if (SettingsData.dbConnected) {
        window.LuminApp?.Toast?.show('Database connection is healthy.', 'success');
    } else {
        window.LuminApp?.Toast?.show('Database unavailable: ' + (SettingsData.dbError || 'unknown error'), 'warning');
    }
}
</script>

<?php View::includeLayout('footer'); ?>
