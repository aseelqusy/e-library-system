/**
 * Luminara Library — Core Application JS
 * Toast system, Command Palette, Modals, Tabs, Search, Ripples
 */

const App = (() => {
    const BASE = document.querySelector('meta[name="base-url"]')?.content || '/library-app/public';

    /* ── Toast System ──────────────────────────── */
    const Toast = {
        container: null,

        init() {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            this.container.setAttribute('role', 'status');
            this.container.setAttribute('aria-live', 'polite');
            document.body.appendChild(this.container);
        },

        show(message, type = 'info', duration = 4000) {
            const icons = { success: '✓', error: '✕', warning: '⚠', info: 'ℹ' };
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <span class="toast-icon">${icons[type] || icons.info}</span>
                <span class="toast-message">${this._escapeHtml(message)}</span>
                <span class="toast-close" aria-label="Close">&times;</span>
            `;
            toast.querySelector('.toast-close').addEventListener('click', () => this._remove(toast));

            this.container.appendChild(toast);

            if (duration > 0) {
                setTimeout(() => this._remove(toast), duration);
            }
        },

        _remove(el) {
            el.classList.add('removing');
            setTimeout(() => el.remove(), 300);
        },

        _escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    };

    /* ── Command Palette ───────────────────────── */
    const CommandPalette = {
        overlay: null,
        input: null,
        results: null,
        items: [],
        highlighted: -1,

        init() {
            this.items = [
                { label: 'Home',                icon: '🏠', url: BASE + '/' },
                { label: 'Browse Catalog',      icon: '📚', url: BASE + '/catalog' },
                { label: 'Categories',           icon: '🏷️', url: BASE + '/catalog/categories' },
                { label: 'Search Books',         icon: '🔍', url: BASE + '/catalog/search' },
                { label: 'Login',               icon: '🔑', url: BASE + '/login' },
                { label: 'Register',            icon: '📝', url: BASE + '/register' },
                { label: 'My Profile',          icon: '👤', url: BASE + '/user/profile' },
                { label: 'My Borrows',          icon: '📖', url: BASE + '/user/borrows' },
                { label: 'Wishlist',            icon: '❤️', url: BASE + '/user/wishlist' },
                { label: 'Borrow History',      icon: '📜', url: BASE + '/user/history' },
                { label: 'Admin Dashboard',     icon: '📊', url: BASE + '/admin' },
                { label: 'Manage Books',        icon: '📕', url: BASE + '/admin/books' },
                { label: 'Manage Users',        icon: '👥', url: BASE + '/admin/users' },
                { label: 'Settings',            icon: '⚙️', url: BASE + '/admin/settings' },
            ];

            // Add book items from page data if available
            const bookData = document.querySelectorAll('[data-book-title]');
            bookData.forEach(el => {
                this.items.push({
                    label: el.dataset.bookTitle,
                    icon: '📖',
                    url: el.dataset.bookUrl || '#'
                });
            });

            this._buildDOM();
            this._bindEvents();
        },

        _buildDOM() {
            this.overlay = document.createElement('div');
            this.overlay.className = 'command-palette-overlay';
            this.overlay.setAttribute('role', 'dialog');
            this.overlay.setAttribute('aria-label', 'Command palette');
            this.overlay.innerHTML = `
                <div class="command-palette">
                    <input type="text" class="command-palette-input" placeholder="Type a command or search..." aria-label="Search commands">
                    <div class="command-palette-results" role="listbox"></div>
                </div>
            `;
            document.body.appendChild(this.overlay);
            this.input = this.overlay.querySelector('.command-palette-input');
            this.results = this.overlay.querySelector('.command-palette-results');
        },

        _bindEvents() {
            document.addEventListener('keydown', e => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    this.toggle();
                }
                if (e.key === 'Escape' && this.overlay.classList.contains('show')) {
                    this.close();
                }
            });

            this.overlay.addEventListener('click', e => {
                if (e.target === this.overlay) this.close();
            });

            this.input.addEventListener('input', () => this._filter());

            this.input.addEventListener('keydown', e => {
                const items = this.results.querySelectorAll('.command-palette-item');
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.highlighted = Math.min(this.highlighted + 1, items.length - 1);
                    this._updateHighlight(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    this.highlighted = Math.max(this.highlighted - 1, 0);
                    this._updateHighlight(items);
                } else if (e.key === 'Enter' && this.highlighted >= 0) {
                    e.preventDefault();
                    items[this.highlighted]?.click();
                }
            });
        },

        toggle() {
            this.overlay.classList.contains('show') ? this.close() : this.open();
        },

        open() {
            this.overlay.classList.add('show');
            this.input.value = '';
            this.highlighted = -1;
            this._filter();
            setTimeout(() => this.input.focus(), 50);
        },

        close() {
            this.overlay.classList.remove('show');
        },

        _filter() {
            const q = this.input.value.toLowerCase();
            const filtered = q
                ? this.items.filter(i => i.label.toLowerCase().includes(q))
                : this.items;

            this.highlighted = -1;

            if (!filtered.length) {
                this.results.innerHTML = '<div class="command-empty">No results found</div>';
                return;
            }

            this.results.innerHTML = filtered.map((item, i) => `
                <div class="command-palette-item" data-url="${this._escapeAttr(item.url)}" role="option" tabindex="-1">
                    <span class="cp-icon">${item.icon}</span>
                    <span class="cp-label">${this._escapeHtml(item.label)}</span>
                </div>
            `).join('');

            this.results.querySelectorAll('.command-palette-item').forEach(el => {
                el.addEventListener('click', () => {
                    window.location.href = el.dataset.url;
                });
            });
        },

        _updateHighlight(items) {
            items.forEach((el, i) => {
                el.classList.toggle('highlighted', i === this.highlighted);
            });
            if (items[this.highlighted]) {
                items[this.highlighted].scrollIntoView({ block: 'nearest' });
            }
        },

        _escapeHtml(str) {
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        },

        _escapeAttr(str) {
            return str.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }
    };

    /* ── Modal System ──────────────────────────── */
    const Modal = {
        open(id) {
            const overlay = document.getElementById(id);
            if (overlay) {
                overlay.classList.add('show');
                document.body.style.overflow = 'hidden';
                const firstInput = overlay.querySelector('input, textarea, select');
                if (firstInput) setTimeout(() => firstInput.focus(), 100);
            }
        },

        close(id) {
            const overlay = document.getElementById(id);
            if (overlay) {
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        },

        init() {
            document.addEventListener('click', e => {
                if (e.target.classList.contains('modal-overlay')) {
                    e.target.classList.remove('show');
                    document.body.style.overflow = '';
                }
                if (e.target.closest('.modal-close')) {
                    const overlay = e.target.closest('.modal-overlay');
                    if (overlay) {
                        overlay.classList.remove('show');
                        document.body.style.overflow = '';
                    }
                }
                if (e.target.dataset.modal) {
                    Modal.open(e.target.dataset.modal);
                }
            });
        }
    };

    /* ── Tabs ───────────────────────────────────── */
    const Tabs = {
        init() {
            document.querySelectorAll('.tabs').forEach(tabGroup => {
                tabGroup.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const target = btn.dataset.tab;
                        const parent = tabGroup.closest('.tab-container') || tabGroup.parentElement;

                        tabGroup.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');

                        parent.querySelectorAll('.tab-pane').forEach(pane => {
                            pane.classList.toggle('active', pane.id === target);
                        });
                    });
                });
            });
        }
    };

    /* ── Notification Dropdown ──────────────────── */
    const Notifications = {
        init() {
            const btn = document.querySelector('.notification-btn');
            const dropdown = document.querySelector('.notification-dropdown');
            if (!btn || !dropdown) return;

            btn.addEventListener('click', e => {
                e.stopPropagation();
                dropdown.classList.toggle('show');
            });

            document.addEventListener('click', e => {
                if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });
        }
    };

    /* ── Ripple Effect ─────────────────────────── */
    const Ripple = {
        init() {
            document.addEventListener('click', e => {
                const btn = e.target.closest('.btn');
                if (!btn) return;

                const circle = document.createElement('span');
                circle.className = 'ripple';
                const rect = btn.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                circle.style.width = circle.style.height = size + 'px';
                circle.style.left = (e.clientX - rect.left - size / 2) + 'px';
                circle.style.top = (e.clientY - rect.top - size / 2) + 'px';

                btn.appendChild(circle);
                setTimeout(() => circle.remove(), 600);
            });
        }
    };

    /* ── Mobile Menu ───────────────────────────── */
    const MobileMenu = {
        init() {
            const toggle = document.querySelector('.menu-toggle');
            const nav = document.querySelector('.navbar-nav');
            if (!toggle || !nav) return;

            toggle.addEventListener('click', () => {
                nav.classList.toggle('mobile-open');
            });

            // Close on link click
            nav.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', () => nav.classList.remove('mobile-open'));
            });
        }
    };

    /* ── Live Search (catalog page) ────────────── */
    const LiveSearch = {
        init() {
            const searchInput = document.getElementById('catalog-search');
            const grid = document.getElementById('book-grid');
            if (!searchInput || !grid) return;

            searchInput.addEventListener('input', () => {
                const q = searchInput.value.toLowerCase();
                const cards = grid.querySelectorAll('.book-card');
                let visible = 0;

                cards.forEach(card => {
                    const title = (card.dataset.title || '').toLowerCase();
                    const author = (card.dataset.author || '').toLowerCase();
                    const show = !q || title.includes(q) || author.includes(q);
                    card.style.display = show ? '' : 'none';
                    if (show) visible++;
                });

                const emptyMsg = grid.querySelector('.empty-state');
                if (emptyMsg) emptyMsg.style.display = visible ? 'none' : 'block';
            });
        }
    };

    /* ── Wishlist Toggle ───────────────────────── */
    const Wishlist = {
        init() {
            document.addEventListener('click', e => {
                const btn = e.target.closest('.wishlist-btn');
                if (!btn) return;

                const bookId = btn.dataset.bookId;

                fetch(BASE + '/user/wishlist/toggle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `book_id=${encodeURIComponent(bookId)}&_token=${encodeURIComponent(document.querySelector('meta[name="csrf-token"]')?.content || '')}`
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'removed') {
                        const card = btn.closest('.book-card');
                        if (card && window.location.pathname.includes('/user/wishlist')) {
                            card.remove();
                        }
                    }
                    btn.classList.toggle('active', data.status === 'added');
                    Toast.show(data.message, data.status === 'error' ? 'error' : 'success');
                })
                .catch(() => Toast.show('Please login to use wishlist', 'warning'));
            });
        }
    };

    /* ── Borrow / Reserve buttons ──────────────── */
    const BorrowActions = {
        init() {
            document.addEventListener('click', e => {
                const btn = e.target.closest('[data-borrow-action]');
                if (!btn) return;

                const action = btn.dataset.borrowAction;
                const bookId = btn.dataset.bookId;
                const url = action === 'reserve' ? '/borrow/reserve' : '/borrow/request';

                fetch(BASE + url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `book_id=${encodeURIComponent(bookId)}&_token=${encodeURIComponent(document.querySelector('meta[name="csrf-token"]')?.content || '')}`
                })
                .then(r => r.json())
                .then(data => {
                    Toast.show(data.message, data.success ? 'success' : 'error');
                })
                .catch(() => Toast.show('Please login first', 'warning'));
            });
        }
    };

    /* ── Counter Animation ─────────────────────── */
    const AnimateCounters = {
        init() {
            const counters = document.querySelectorAll('[data-count]');
            if (!counters.length) return;

            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;
                    const el = entry.target;
                    const target = parseInt(el.dataset.count);
                    this._animate(el, target);
                    observer.unobserve(el);
                });
            }, { threshold: 0.5 });

            counters.forEach(c => observer.observe(c));
        },

        _animate(el, target) {
            let current = 0;
            const step = target / 60;
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                el.textContent = Math.floor(current).toLocaleString();
            }, 16);
        }
    };

    /* ── Flash Messages from PHP ───────────────── */
    const FlashMessages = {
        init() {
            document.querySelectorAll('.flash-message').forEach(el => {
                const type = el.classList.contains('success') ? 'success'
                    : el.classList.contains('error') ? 'error'
                    : 'info';
                Toast.show(el.textContent.trim(), type);
                el.remove();
            });
        }
    };

    /* ── Admin Sidebar Toggle ──────────────────── */
    const AdminSidebar = {
        init() {
            const toggle = document.querySelector('.sidebar-toggle');
            const sidebar = document.querySelector('.admin-sidebar');
            if (!toggle || !sidebar) return;

            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });

            document.addEventListener('click', e => {
                if (window.innerWidth <= 1024 &&
                    !sidebar.contains(e.target) &&
                    !toggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            });
        }
    };

    /* ── Init ───────────────────────────────────── */
    function init() {
        Toast.init();
        CommandPalette.init();
        Modal.init();
        Tabs.init();
        Notifications.init();
        Ripple.init();
        MobileMenu.init();
        LiveSearch.init();
        Wishlist.init();
        BorrowActions.init();
        AnimateCounters.init();
        AdminSidebar.init();

        // Show flash messages after Toast is ready
        setTimeout(() => FlashMessages.init(), 100);
    }

    document.addEventListener('DOMContentLoaded', init);

    return { Toast, Modal, CommandPalette };
})();
