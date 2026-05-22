<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="admin-layout">
    <?php View::partial('admin/sidebar'); ?>

    <main class="admin-main">
        <div class="flex items-center justify-between mb-6" style="justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2>📈 Reports</h2>
                <p class="text-muted text-sm">Library analytics and export tools</p>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="kpi-grid mb-6">
            <div class="kpi-card glass-card">
                <div class="kpi-icon purple">📚</div>
                <div>
                    <div class="kpi-value"><?= $stats['total_books'] ?? 0 ?></div>
                    <div class="kpi-label">Total Books</div>
                </div>
            </div>
            <div class="kpi-card glass-card">
                <div class="kpi-icon cyan">📖</div>
                <div>
                    <div class="kpi-value"><?= $stats['total_borrows'] ?? 0 ?></div>
                    <div class="kpi-label">Total Borrows</div>
                </div>
            </div>
            <div class="kpi-card glass-card">
                <div class="kpi-icon amber">⭐</div>
                <div>
                    <div class="kpi-value"><?= $stats['avg_rating'] ?? '0.0' ?></div>
                    <div class="kpi-label">Avg Rating</div>
                </div>
            </div>
            <div class="kpi-card glass-card">
                <div class="kpi-icon red">⚠</div>
                <div>
                    <div class="kpi-value"><?= $stats['overdue'] ?? 0 ?></div>
                    <div class="kpi-label">Overdue Now</div>
                </div>
            </div>
        </div>

        <!-- Report Cards -->
        <div class="book-grid" style="grid-template-columns:repeat(auto-fill,minmax(300px,1fr));">
            <div class="card glass-card" style="padding:24px;">
                <h3 class="mb-2">📊 Borrowing Report</h3>
                <p class="text-muted text-sm mb-4">Overview of all borrowing activity including borrows, returns, and overdue items.</p>
                <div class="flex gap-2">
                    <button class="btn btn-primary btn-sm" onclick="exportReport('borrows', 'csv')">📥 Export CSV</button>
                    <button class="btn btn-secondary btn-sm" onclick="exportReport('borrows', 'pdf')">📄 Export PDF</button>
                </div>
            </div>

            <div class="card glass-card" style="padding:24px;">
                <h3 class="mb-2">📚 Inventory Report</h3>
                <p class="text-muted text-sm mb-4">Complete catalog listing with availability, condition, and category breakdown.</p>
                <div class="flex gap-2">
                    <button class="btn btn-primary btn-sm" onclick="exportReport('inventory', 'csv')">📥 Export CSV</button>
                    <button class="btn btn-secondary btn-sm" onclick="exportReport('inventory', 'pdf')">📄 Export PDF</button>
                </div>
            </div>

            <div class="card glass-card" style="padding:24px;">
                <h3 class="mb-2">👥 User Activity Report</h3>
                <p class="text-muted text-sm mb-4">User registration trends, most active members, and engagement metrics.</p>
                <div class="flex gap-2">
                    <button class="btn btn-primary btn-sm" onclick="exportReport('users', 'csv')">📥 Export CSV</button>
                    <button class="btn btn-secondary btn-sm" onclick="exportReport('users', 'pdf')">📄 Export PDF</button>
                </div>
            </div>

            <div class="card glass-card" style="padding:24px;">
                <h3 class="mb-2">🏆 Popular Books Report</h3>
                <p class="text-muted text-sm mb-4">Most borrowed, highest rated, and trending titles in the library.</p>
                <div class="flex gap-2">
                    <button class="btn btn-primary btn-sm" onclick="exportReport('popular', 'csv')">📥 Export CSV</button>
                    <button class="btn btn-secondary btn-sm" onclick="exportReport('popular', 'pdf')">📄 Export PDF</button>
                </div>
            </div>

            <div class="card glass-card" style="padding:24px;">
                <h3 class="mb-2">⏰ Overdue Report</h3>
                <p class="text-muted text-sm mb-4">Currently overdue items, late fees, and delinquent accounts.</p>
                <div class="flex gap-2">
                    <button class="btn btn-primary btn-sm" onclick="exportReport('overdue', 'csv')">📥 Export CSV</button>
                    <button class="btn btn-secondary btn-sm" onclick="exportReport('overdue', 'pdf')">📄 Export PDF</button>
                </div>
            </div>

            <div class="card glass-card" style="padding:24px;">
                <h3 class="mb-2">📅 Monthly Summary</h3>
                <p class="text-muted text-sm mb-4">Month-by-month summary of library operations and key metrics.</p>
                <div class="flex gap-2">
                    <button class="btn btn-primary btn-sm" onclick="exportReport('monthly', 'csv')">📥 Export CSV</button>
                    <button class="btn btn-secondary btn-sm" onclick="exportReport('monthly', 'pdf')">📄 Export PDF</button>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    function exportReport(type, format) {
        // 1. التحقق إذا كان الطلب لـ PDF (تنبيه بعدم الدعم حالياً لتوفير المكتبات)
        if (format === 'pdf') {
            if (window.LuminApp?.Toast?.show) {
                window.LuminApp.Toast.show('PDF format is coming soon! Please use CSV download.', 'info');
            } else {
                alert('PDF format is coming soon! Please use CSV download.');
            }
            return;
        }

        // 2. جلب الرابط الأساسي للمشروع من الوسم meta المتواجد بالـ Header
        const baseUrlElement = document.querySelector('meta[name="base-url"]');
        const baseUrl = baseUrlElement ? baseUrlElement.content : '';

        // 3. بناء الرابط الموجه للـ Controller المخصص للتقارير
        // المسار المعتمد: baseUrl + /admin/reports/export?type=...&format=csv
        const exportUrl = `${baseUrl}/admin/reports/export?type=${type}&format=${format}`;

        if (window.LuminApp?.Toast?.show) {
            window.LuminApp.Toast.show('Preparing your report download...', 'success');
        }

        // 4. إجبار المتصفح على فتح الرابط في نافذة مخفية لبدء التنزيل التلقائي دون قفز أو وميض بالصفحة
        const downloadAnchor = document.createElement('a');
        downloadAnchor.href = exportUrl;
        downloadAnchor.style.display = 'none';
        document.body.appendChild(downloadAnchor);
        downloadAnchor.click();
        document.body.removeChild(downloadAnchor);
    }
</script>

<?php View::includeLayout('footer'); ?>
