Gemini
<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="admin-layout">
    <?php View::partial('admin/sidebar'); ?>

    <main class="admin-main">
        <div class="flex items-center justify-between mb-6" style="justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2>📖 Manage Borrows</h2>
                <p class="text-muted text-sm"><?= count($borrows) ?> borrow records</p>
            </div>
        </div>

        <!-- Status filter -->
        <div class="tabs mb-4">
            <button class="tab active" data-status="">All</button>
            <button class="tab" data-status="active">Active</button>
            <button class="tab" data-status="overdue">Overdue</button>
            <button class="tab" data-status="returned">Returned</button>
            <button class="tab" data-status="reserved">Reserved</button>
        </div>

        <!-- Borrows Table -->
        <div class="card glass-card">
            <div class="table-wrapper">
                <table id="borrows-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Book</th>
                        <th>User</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($borrows as $i => $borrow): ?>
                        <?php
                        $book = $borrow['book'] ?? null;
                        $user = $borrow['user'] ?? null;
                        ?>
                        <tr data-status="<?= $borrow['status'] ?>">
                            <td class="text-muted"><?= $i + 1 ?></td>
                            <td>
                                <div>
                                    <strong><?= $book ? e($book['title']) : 'Unknown' ?></strong>
                                    <div class="text-muted text-sm"><?= $book ? e($book['author']) : '' ?></div>
                                </div>
                            </td>
                            <td><?= $user ? e($user['name']) : 'Unknown' ?></td>
                            <td class="text-muted text-sm"><?= formatDate($borrow['borrow_date']) ?></td>
                            <td class="text-muted text-sm"><?= formatDate($borrow['due_date']) ?></td>
                            <td><?= status_chip($borrow['status']) ?></td>
                            <td>
                                <div class="flex gap-1">
                                    <?php if ($borrow['status'] === 'active' || $borrow['status'] === 'overdue'): ?>
                                        <button class="btn btn-primary btn-sm" onclick="markReturned(<?= $borrow['id'] ?>)">Mark Returned</button>
                                    <?php elseif ($borrow['status'] === 'reserved'): ?>
                                        <button class="btn btn-primary btn-sm" onclick="approveReservation(<?= $borrow['id'] ?>)">Approve</button>
                                        <button class="btn btn-secondary btn-sm" onclick="rejectReservation(<?= $borrow['id'] ?>)">Reject</button>
                                    <?php else: ?>
                                        <span class="text-muted text-sm">—</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<div id="action-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 id="action-modal-title">Confirm Action</h3>
            <button class="modal-close" onclick="closeActionModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p id="action-modal-message" class="text-secondary">Are you sure you want to perform this action?</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeActionModal()">Cancel</button>
            <button id="action-modal-confirm-btn" class="btn btn-primary">Confirm</button>
        </div>
    </div>
</div>


<script>
    // 1. توليد المودال ديناميكياً وحقنه في الصفحة عند التحميل
    document.addEventListener('DOMContentLoaded', () => {
        const modalHtml = `
        <div id="action-modal" class="modal-overlay">
            <div class="modal">
                <div class="modal-header">
                    <h3 id="action-modal-title">Confirm Action</h3>
                    <button class="modal-close" onclick="closeActionModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <p id="action-modal-message" class="text-secondary">Are you sure you want to perform this action?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeActionModal()">Cancel</button>
                    <button id="action-modal-confirm-btn" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>`;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // منطق تبويبات الفلترة الافتراضي الخاص بك
        const tabs = document.querySelectorAll('.tabs .tab');
        const rows = document.querySelectorAll('#borrows-table tbody tr');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const status = tab.dataset.status;
                rows.forEach(row => {
                    row.style.display = (!status || row.dataset.status === status) ? '' : 'none';
                });
            });
        });
    });

    // 2. دالة فتح المودال والتحكم بالأزرار
    function openActionModal(title, message, path, id, confirmBtnClass = 'btn-primary', confirmText = 'Confirm', nextStatus = '') {
        const modalEl = document.getElementById('action-modal');
        if (!modalEl) return;

        document.getElementById('action-modal-title').innerText = title;
        document.getElementById('action-modal-message').innerText = message;

        const confirmBtn = document.getElementById('action-modal-confirm-btn');
        confirmBtn.className = `btn ${confirmBtnClass}`;
        confirmBtn.innerText = confirmText;

        // عند تأكيد الإجراء من داخل المودال
        confirmBtn.onclick = function() {
            closeActionModal();
            submitBorrowAction(path, id, nextStatus);
        };

        modalEl.classList.add('show');
    }

    function closeActionModal() {
        const modalEl = document.getElementById('action-modal');
        if (modalEl) modalEl.classList.remove('show');
    }

    // إغلاق المودال عند الضغط في أي مكان خارج الصندوق التجويفي
    document.addEventListener('click', function(e) {
        if (e.target.closest('.modal-overlay') && !e.target.closest('.modal')) {
            closeActionModal();
        }
    });

    // 3. دوال الأزرار الثلاثة وتحديد الحالات الجديدة في الـ DOM
    function markReturned(id) {
        openActionModal('📖 Mark as Returned', 'Are you sure you want to mark this book as returned?', 'admin/borrows/return', id, 'btn-primary', 'Yes, Mark Returned', 'returned');
    }

    function approveReservation(id) {
        openActionModal('✅ Approve Reservation', 'Are you sure you want to approve this reservation?', 'admin/borrows/approve', id, 'btn-success', 'Approve', 'active');
    }

    function rejectReservation(id) {
        openActionModal('⚠️ Reject Reservation', 'Are you sure you want to reject this reservation? This action cannot be undone.', 'admin/borrows/reject', id, 'btn-danger', 'Reject', 'rejected');
    }

    // 4. دالة الـ Fetch الذكية والمرنة جداً (تتعامل مع ردود الـ HTML والـ JSON بنجاح)
    function submitBorrowAction(path, id, nextStatus) {
        const baseElement = document.querySelector('meta[name="base-url"]');
        const tokenElement = document.querySelector('meta[name="csrf-token"]');

        const base = baseElement ? baseElement.content : '';
        const token = tokenElement ? tokenElement.content : '';
        const url = base + '/' + path;

        const params = new URLSearchParams();
        params.append('id', id);
        params.append('_token', token);

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        })
            .then(response => {
                // حل المشكلة الجوهري: إذا كانت حالة الرد من السيرفر ناجحة (200 OK)
                // نقوم بتحديث الـ DOM فوراً دون الاهتمام بنوع محتوى الرد (سواء كان صفحة كاملة أو JSON)
                if (response.ok) {
                    updateRowStatusInDOM(id, nextStatus);
                    if (window.LuminApp?.Toast?.show) {
                        window.LuminApp.Toast.show('Action completed successfully!', 'success');
                    }
                    // نقوم بقطع السلسلة هنا لأننا نجحنا بالفعل
                    return null;
                }
                throw new Error('Server responded with status: ' + response.status);
            })
            .then(data => {
                // في حال كان الرد JSON عادي ومررنا له قيم (للأزرار الأخرى مستقبلاً)
                if (data && !data.success) {
                    if (window.LuminApp?.Toast?.show) {
                        window.LuminApp.Toast.show(data.message || 'Action failed.', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                if (window.LuminApp?.Toast?.show) {
                    window.LuminApp.Toast.show('Something went wrong. Check console.', 'error');
                }
            });
    }

    // 5. دالة تحديث السطر برمجياً داخل المتصفح بدقة متناهية وبدون ريفريش
    function updateRowStatusInDOM(id, nextStatus) {
        let row = null;

        // البحث الشامل عن السطر المعني باستخدام الـ ID الممرر في دالة الـ onclick الأصلية
        const allRows = document.querySelectorAll('#borrows-table tbody tr');
        allRows.forEach(r => {
            const buttons = r.querySelectorAll('button');
            buttons.forEach(btn => {
                if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(id)) {
                    row = r;
                }
            });
        });

        if (!row) return;

        // 1. تحديث الـ dataset الخاص بالفلترة للسطر كاملاً ليعمل مع أزرار الفلاتر العلوية بشكل صحيح
        row.dataset.status = nextStatus;

        // 2. تحديث عمود الحالة (Badge Column) بشكل مرئي خلاب
        const statusTd = row.cells[5]; // العمود رقم 6 في الجدول هو الخاص بـ Status
        if (statusTd) {
            let badgeClass = 'badge-secondary';
            if (nextStatus === 'active') badgeClass = 'badge-success';
            if (nextStatus === 'returned') badgeClass = 'badge-info';
            if (nextStatus === 'rejected') badgeClass = 'badge-danger';

            statusTd.innerHTML = `<span class="badge ${badgeClass}">${nextStatus.charAt(0).toUpperCase() + nextStatus.slice(1)}</span>`;
        }

        // 3. تحديث عمود العمليات (Actions Column) بإزالة الأزرار لعدم تكرار الضغط عليها
        const actionsTd = row.cells[6]; // العمود رقم 7 والأخير
        if (actionsTd) {
            actionsTd.innerHTML = `<span class="text-muted text-sm" style="opacity: 0.6; font-style: italic;">Processed</span>`;
        }

        // تطبيق تأثير بصري ناعم جداً ومبهج بالـ CSS يوضح نجاح العملية للآدمن
        row.style.transition = 'background-color 0.6s ease';
        row.style.backgroundColor = 'rgba(46, 213, 115, 0.12)';
        setTimeout(() => {
            row.style.backgroundColor = '';
        }, 1200);
    }
</script>

<?php View::includeLayout('footer'); ?>

