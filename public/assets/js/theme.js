/**
 * theme.js — Luminara Library dark/light mode toggle
 * Strategy: sets data-theme on <html> AND injects a <style> tag
 * with !important overrides so specificity wars can't block the theme.
 */

(function () {
    'use strict';

    const STORAGE_KEY = 'luminara_theme';
    const COOKIE_NAME = 'theme';
    const DARK  = 'dark';
    const LIGHT = 'light';

    /* ─── Light theme CSS injected at runtime ───────────────────────
       Uses !important so it wins over any specificity in styles.css.
       Targets every pattern used in the Luminara views.
    ─────────────────────────────────────────────────────────────── */
    const LIGHT_CSS = `
        /* ── Base ─────────────────────────────── */
        [data-theme="light"] {
            --bg:             #f0f2f8 !important;
            --bg-secondary:   #e4e7f0 !important;
            --bg-tertiary:    #d8dcea !important;
            --text:           #1a1a2e !important;
            --text-secondary: #3a3a5c !important;
            --text-muted:     #5c5c80 !important;
            --glass-bg:       rgba(255,255,255,0.70) !important;
            --glass-border:   rgba(100,60,200,0.20) !important;
            --glass-shadow:   0 8px 32px rgba(80,60,160,0.10) !important;
            --border:         rgba(100,60,200,0.18) !important;
            --navbar-bg:      rgba(235,237,248,0.92) !important;
            color-scheme: light;
        }

        /* ── Body & page ──────────────────────── */
        [data-theme="light"] body {
            background: #f0f2f8 !important;
            color: #1a1a2e !important;
        }
        [data-theme="light"] .page-wrapper {
            background: #f0f2f8 !important;
        }

        /* ── Navbar ───────────────────────────── */
        [data-theme="light"] .navbar {
            background: rgba(235,237,248,0.92) !important;
            border-bottom: 1px solid rgba(100,60,200,0.15) !important;
            backdrop-filter: blur(20px) !important;
        }
        [data-theme="light"] .navbar .nav-link,
        [data-theme="light"] .navbar .navbar-brand {
            color: #1a1a2e !important;
        }
        [data-theme="light"] .navbar .nav-link:hover,
        [data-theme="light"] .navbar .nav-link.active {
            color: #7c3aed !important;
        }
        [data-theme="light"] .navbar-search-btn {
            background: rgba(255,255,255,0.6) !important;
            color: #5c5c80 !important;
            border-color: rgba(100,60,200,0.18) !important;
        }

        /* ── Glass cards ──────────────────────── */
        [data-theme="light"] .glass-card {
            background: rgba(255,255,255,0.70) !important;
            border-color: rgba(100,60,200,0.20) !important;
            box-shadow: 0 8px 32px rgba(80,60,160,0.10) !important;
            color: #1a1a2e !important;
        }

        /* ── Generic cards ────────────────────── */
        [data-theme="light"] .card {
            background: rgba(255,255,255,0.70) !important;
            border-color: rgba(100,60,200,0.18) !important;
            color: #1a1a2e !important;
        }

        /* ── Text utilities ───────────────────── */
        [data-theme="light"] .text-muted {
            color: #5c5c80 !important;
        }
        [data-theme="light"] h1,
        [data-theme="light"] h2,
        [data-theme="light"] h3,
        [data-theme="light"] h4,
        [data-theme="light"] p,
        [data-theme="light"] span,
        [data-theme="light"] li,
        [data-theme="light"] td,
        [data-theme="light"] th,
        [data-theme="light"] label {
            color: inherit !important;
        }

        /* ── Forms ────────────────────────────── */
        [data-theme="light"] .form-control,
        [data-theme="light"] input,
        [data-theme="light"] textarea,
        [data-theme="light"] select {
            background: rgba(255,255,255,0.85) !important;
            border-color: rgba(100,60,200,0.20) !important;
            color: #1a1a2e !important;
        }
        [data-theme="light"] .form-control::placeholder,
        [data-theme="light"] input::placeholder,
        [data-theme="light"] textarea::placeholder {
            color: #8888a8 !important;
        }
        [data-theme="light"] .form-label {
            color: #3a3a5c !important;
        }
        [data-theme="light"] .input-icon {
            color: #5c5c80 !important;
        }

        /* ── Buttons ──────────────────────────── */
        [data-theme="light"] .btn-ghost {
            color: #1a1a2e !important;
        }
        [data-theme="light"] .btn-ghost:hover {
            background: rgba(100,60,200,0.08) !important;
        }
        [data-theme="light"] .btn-secondary {
            background: rgba(100,60,200,0.08) !important;
            color: #1a1a2e !important;
            border-color: rgba(100,60,200,0.18) !important;
        }
        [data-theme="light"] .btn-secondary:hover {
            background: rgba(100,60,200,0.14) !important;
        }
        [data-theme="light"] .btn-outline {
            color: #7c3aed !important;
            border-color: #7c3aed !important;
        }

        /* ── Tables ───────────────────────────── */
        [data-theme="light"] table {
            color: #1a1a2e !important;
        }
        [data-theme="light"] thead tr,
        [data-theme="light"] thead th {
            background: rgba(100,60,200,0.06) !important;
            color: #3a3a5c !important;
            border-color: rgba(100,60,200,0.12) !important;
        }
        [data-theme="light"] tbody tr {
            border-color: rgba(100,60,200,0.08) !important;
        }
        [data-theme="light"] tbody tr:hover {
            background: rgba(100,60,200,0.04) !important;
        }
        [data-theme="light"] td {
            color: #1a1a2e !important;
            border-color: rgba(100,60,200,0.08) !important;
        }

        /* ── Admin sidebar ────────────────────── */
        [data-theme="light"] .admin-sidebar {
            background: rgba(225,228,245,0.95) !important;
            border-right-color: rgba(100,60,200,0.15) !important;
        }
        [data-theme="light"] .sidebar-link {
            color: #3a3a5c !important;
        }
        [data-theme="light"] .sidebar-link:hover,
        [data-theme="light"] .sidebar-link.active {
            background: rgba(100,60,200,0.10) !important;
            color: #7c3aed !important;
        }
        [data-theme="light"] .sidebar-label {
            color: #8888a8 !important;
        }

        /* ── KPI / stat cards ─────────────────── */
        [data-theme="light"] .kpi-card,
        [data-theme="light"] .stat-card {
            background: rgba(255,255,255,0.70) !important;
            border-color: rgba(100,60,200,0.18) !important;
        }
        [data-theme="light"] .kpi-value,
        [data-theme="light"] .stat-value {
            color: #1a1a2e !important;
        }
        [data-theme="light"] .kpi-label,
        [data-theme="light"] .stat-label {
            color: #5c5c80 !important;
        }
        [data-theme="light"] .kpi-trend {
            color: #8888a8 !important;
        }

        /* ── Section backgrounds ──────────────── */
        [data-theme="light"] .section {
            background: transparent !important;
        }
        [data-theme="light"] .section:nth-child(even),
        [data-theme="light"] section[style*="bg-secondary"] {
            background: #e4e7f0 !important;
        }

        /* ── Hero ─────────────────────────────── */
        [data-theme="light"] .hero {
            background: transparent !important;
        }
        [data-theme="light"] .hero-title,
        [data-theme="light"] .hero-subtitle {
            color: #1a1a2e !important;
        }

        /* ── Modals ───────────────────────────── */
        [data-theme="light"] .modal {
            background: rgba(240,242,248,0.98) !important;
            border-color: rgba(100,60,200,0.18) !important;
            color: #1a1a2e !important;
        }
        [data-theme="light"] .modal-overlay {
            background: rgba(20,10,40,0.40) !important;
        }
        [data-theme="light"] .modal-header {
            border-bottom-color: rgba(100,60,200,0.12) !important;
        }
        [data-theme="light"] .modal-footer {
            border-top-color: rgba(100,60,200,0.12) !important;
        }

        /* ── Notifications dropdown ───────────── */
        [data-theme="light"] .notification-dropdown {
            background: rgba(240,242,248,0.98) !important;
            border-color: rgba(100,60,200,0.18) !important;
            color: #1a1a2e !important;
        }
        [data-theme="light"] .notification-item {
            border-bottom-color: rgba(100,60,200,0.08) !important;
            color: #1a1a2e !important;
        }
        [data-theme="light"] .notification-item.unread {
            background: rgba(124,58,237,0.06) !important;
        }
        [data-theme="light"] .notif-message {
            color: #1a1a2e !important;
        }
        [data-theme="light"] .notif-time {
            color: #8888a8 !important;
        }
        [data-theme="light"] .notification-dropdown-header {
            color: #3a3a5c !important;
            border-bottom-color: rgba(100,60,200,0.12) !important;
        }

        /* ── Auth cards ───────────────────────── */
        [data-theme="light"] .auth-card,
        [data-theme="light"] .auth-page {
            background: transparent !important;
        }
        [data-theme="light"] .auth-card .glass-card {
            background: rgba(255,255,255,0.80) !important;
        }
        [data-theme="light"] .auth-header h2,
        [data-theme="light"] .auth-header p {
            color: #1a1a2e !important;
        }
        [data-theme="light"] .auth-footer {
            color: #5c5c80 !important;
        }

        /* ── Book cards ───────────────────────── */
        [data-theme="light"] .book-card {
            background: rgba(255,255,255,0.70) !important;
            border-color: rgba(100,60,200,0.18) !important;
            color: #1a1a2e !important;
        }
        [data-theme="light"] .book-title {
            color: #1a1a2e !important;
        }
        [data-theme="light"] .book-author {
            color: #5c5c80 !important;
        }

        /* ── Category cards ───────────────────── */
        [data-theme="light"] .category-card {
            background: rgba(255,255,255,0.70) !important;
            border-color: rgba(100,60,200,0.18) !important;
        }
        [data-theme="light"] .cat-name {
            color: #1a1a2e !important;
        }
        [data-theme="light"] .cat-count {
            color: #5c5c80 !important;
        }

        /* ── Chips ────────────────────────────── */
        [data-theme="light"] .chip-outline {
            border-color: #7c3aed !important;
            color: #7c3aed !important;
            background: transparent !important;
        }
        [data-theme="light"] .chip-muted {
            background: rgba(100,60,200,0.08) !important;
            color: #5c5c80 !important;
        }

        /* ── Profile ──────────────────────────── */
        [data-theme="light"] .profile-header {
            background: rgba(255,255,255,0.70) !important;
        }
        [data-theme="light"] .profile-email {
            color: #5c5c80 !important;
        }

        /* ── Timeline ─────────────────────────── */
        [data-theme="light"] .timeline-content {
            background: rgba(255,255,255,0.70) !important;
            border-color: rgba(100,60,200,0.18) !important;
        }
        [data-theme="light"] .timeline-date {
            color: #5c5c80 !important;
        }

        /* ── Filter bar ───────────────────────── */
        [data-theme="light"] .filter-bar {
            background: rgba(255,255,255,0.60) !important;
            border-color: rgba(100,60,200,0.15) !important;
        }

        /* ── Flash messages ───────────────────── */
        [data-theme="light"] .flash-message.success {
            background: rgba(16,185,129,0.12) !important;
            color: #065f46 !important;
        }
        [data-theme="light"] .flash-message.error {
            background: rgba(239,68,68,0.12) !important;
            color: #991b1b !important;
        }

        /* ── Tabs ─────────────────────────────── */
        [data-theme="light"] .tabs {
            border-bottom-color: rgba(100,60,200,0.15) !important;
        }
        [data-theme="light"] .tab,
        [data-theme="light"] .tab-btn {
            color: #5c5c80 !important;
        }
        [data-theme="light"] .tab.active,
        [data-theme="light"] .tab-btn.active {
            color: #7c3aed !important;
            border-bottom-color: #7c3aed !important;
        }

        /* ── Scrollbars ───────────────────────── */
        [data-theme="light"] ::-webkit-scrollbar-track {
            background: #e4e7f0 !important;
        }
        [data-theme="light"] ::-webkit-scrollbar-thumb {
            background: rgba(100,60,200,0.25) !important;
        }

        /* ── Chart containers ─────────────────── */
        [data-theme="light"] .chart-container {
            background: rgba(255,255,255,0.70) !important;
        }

        /* ── Admin main area ──────────────────── */
        [data-theme="light"] .admin-main {
            background: transparent !important;
        }
        [data-theme="light"] .admin-layout {
            background: #f0f2f8 !important;
        }

        /* ── User avatar ──────────────────────── */
        [data-theme="light"] .user-avatar {
            background: rgba(100,60,200,0.12) !important;
            color: #7c3aed !important;
        }

        /* ── Book detail ──────────────────────── */
        [data-theme="light"] .book-detail-hero {
            background: transparent !important;
        }
        [data-theme="light"] .meta-item {
            background: rgba(255,255,255,0.70) !important;
        }
        [data-theme="light"] .meta-label {
            color: #5c5c80 !important;
        }
        [data-theme="light"] .meta-value {
            color: #1a1a2e !important;
        }

        /* ── Review cards ─────────────────────── */
        [data-theme="light"] .review-card {
            border-bottom-color: rgba(100,60,200,0.08) !important;
        }
        [data-theme="light"] .reviewer-name,
        [data-theme="light"] .review-comment {
            color: #1a1a2e !important;
        }
        [data-theme="light"] .review-date {
            color: #8888a8 !important;
        }

        /* ── Misc ─────────────────────────────── */
        [data-theme="light"] hr {
            border-color: rgba(100,60,200,0.12) !important;
        }
        [data-theme="light"] kbd {
            background: rgba(255,255,255,0.70) !important;
            border-color: rgba(100,60,200,0.18) !important;
            color: #3a3a5c !important;
        }
        [data-theme="light"] .error-code {
            color: rgba(100,60,200,0.15) !important;
        }

        /* ── Theme toggle button ──────────────── */
        #theme-toggle {
            font-size: 1.1rem;
            transition: transform 0.3s ease;
            padding: 6px 8px;
        }
        #theme-toggle:hover {
            transform: rotate(18deg) scale(1.15);
        }
    `;

    /* ─── Inject the <style> tag once ──────────────────────────── */
    function injectStyles() {
        if (document.getElementById('luminara-light-theme')) return;
        const el = document.createElement('style');
        el.id = 'luminara-light-theme';
        el.textContent = LIGHT_CSS;
        document.head.appendChild(el);
    }

    /* ─── Read saved preference; default to dark ────────────────── */
    function getSavedTheme() {
        try {
            return localStorage.getItem(STORAGE_KEY) || DARK;
        } catch (_) {
            return DARK;
        }
    }

    /* ─── Apply theme to <html> and update button icon ──────────── */
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);

        const icon = document.getElementById('theme-icon');
        if (icon) {
            icon.textContent = theme === DARK ? '☀️' : '🌙';
        }

        const btn = document.getElementById('theme-toggle');
        if (btn) {
            btn.title = theme === DARK ? 'Switch to light mode' : 'Switch to dark mode';
            btn.setAttribute('aria-label', btn.title);
        }
    }

    /* ─── Persist to localStorage + 1-year cookie ───────────────── */
    function saveTheme(theme) {
        try {
            localStorage.setItem(STORAGE_KEY, theme);
        } catch (_) {}
        const exp = new Date();
        exp.setFullYear(exp.getFullYear() + 1);
        document.cookie = `${COOKIE_NAME}=${theme};expires=${exp.toUTCString()};path=/;SameSite=Lax`;
    }

    /* ─── Toggle ────────────────────────────────────────────────── */
    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme') || DARK;
        const next    = current === DARK ? LIGHT : DARK;
        applyTheme(next);
        saveTheme(next);
    }

    /* ─── Boot: inject styles + apply saved theme before first paint */
    injectStyles();
    applyTheme(getSavedTheme());

    /* ─── Wire button after DOM ready ───────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('theme-toggle');
        if (btn) {
            btn.addEventListener('click', toggleTheme);
        }
        applyTheme(getSavedTheme());
    });

})();