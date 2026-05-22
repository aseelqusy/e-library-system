/**
 * Book Cover Image System
 * Handles fallback loading for book cover images with multiple sources
 */

class BookCoverManager {
    constructor() {
        this.placeholderSvg = this.getPlaceholderSvg();
        this.coverCache = {};
        this.init();
    }

    /**
     * Initialize the book cover manager
     */
    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.setupImageFallbacks();
        });

        // Also run immediately in case DOM is already ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupImageFallbacks());
        } else {
            this.setupImageFallbacks();
        }
    }

    /**
     * Setup fallback handlers for all book cover images
     */
    setupImageFallbacks() {
        document.querySelectorAll('[data-book-cover-img]').forEach(img => {
            this.setupFallback(img);
        });

        // Also handle images inside .book-cover containers
        document.querySelectorAll('.book-cover img').forEach(img => {
            this.setupFallback(img);
        });

        // Handle order card covers
        document.querySelectorAll('.order-card-cover img').forEach(img => {
            this.setupFallback(img);
        });

        // Handle detail page cover
        document.querySelectorAll('.book-detail-cover img').forEach(img => {
            this.setupFallback(img);
        });

    }

    /**
     * Setup error handler for a single image
     */
    setupFallback(img) {
        const bookId = img.dataset.bookId || 'unknown';
        const isbn = img.dataset.isbn || '';

        img.onerror = () => {
            const container = img.closest('.book-cover') || img.closest('.order-card-cover') || img.closest('.book-detail-cover');

            // Try next fallback
            const nextFallback = this.getNextFallback(isbn, img.src);
            if (nextFallback) {
                img.src = nextFallback;
            } else {
                // No more fallbacks, show placeholder
                this.showPlaceholder(img, container);
            }
        };

        img.onerror.fallbackStep = 1;
    }

    /**
     * Get next fallback source for an image
     */
    getNextFallback(isbn, currentSrc) {
        const step = (navigator.bookCoverFallbackStep = (navigator.bookCoverFallbackStep || 0) + 1);

        if (!isbn || isbn.trim() === '') {
            return null;
        }

        const cleanIsbn = this.cleanIsbn(isbn);
        if (!cleanIsbn) {
            return null;
        }

        // Step 1: Try OpenLibrary
        if (step === 1) {
            return `https://covers.openlibrary.org/b/isbn/${cleanIsbn}-L.jpg`;
        }

        // Step 2: Try Google Books with thumbnail
        if (step === 2) {
            return this.getGoogleBooksUrl(cleanIsbn);
        }

        // Step 3: Try alternative OpenLibrary endpoint
        if (step === 3) {
            return `https://covers.openlibrary.org/b/isbn/${cleanIsbn}-M.jpg`;
        }

        return null;
    }

    /**
     * Clean and normalize ISBN
     */
    cleanIsbn(isbn) {
        if (!isbn) return null;

        // Remove dashes, spaces, etc.
        let clean = isbn.replace(/[^0-9X]/gi, '').toUpperCase();

        // Valid ISBN-10 or ISBN-13
        if (clean.length === 10 || clean.length === 13) {
            return clean;
        }

        return null;
    }

    /**
     * Get Google Books cover URL
     */
    getGoogleBooksUrl(isbn) {
        // This returns a direct thumbnail URL if we have one cached
        // Otherwise return null to try next fallback
        return null; // Modified to not make API calls from the client
    }

    /**
     * Show placeholder image
     */
    showPlaceholder(img, container) {
        img.style.display = 'none';

        if (container) {
            // Clear container and add placeholder
            container.innerHTML = '';

            const placeholder = document.createElement('div');
            placeholder.style.cssText = `
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: rgba(255, 255, 255, 0.6);
                font-size: 3rem;
                font-weight: bold;
            `;
            placeholder.innerHTML = '📖';
            placeholder.setAttribute('aria-label', 'Book cover unavailable');
            container.appendChild(placeholder);
        }
    }

    /**
     * Get default placeholder SVG
     */
    getPlaceholderSvg() {
        return 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 450" preserveAspectRatio="xMidYMid meet">' +
            '<defs><linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">' +
            '<stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />' +
            '<stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />' +
            '</linearGradient></defs>' +
            '<rect width="300" height="450" fill="url(#grad)"/>' +
            '<rect x="20" y="30" width="260" height="390" rx="8" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2"/>' +
            '<text x="150" y="230" font-size="80" text-anchor="middle" fill="rgba(255,255,255,0.4)" font-family="Arial, sans-serif">📖</text>' +
            '</svg>'
        );
    }

    /**
     * Preload a book cover image
     */
    preload(isbn, onSuccess, onError) {
        const img = new Image();

        img.onload = () => {
            if (onSuccess) onSuccess(img.src);
        };

        img.onerror = () => {
            if (onError) onError();
        };

        const cleanIsbn = this.cleanIsbn(isbn);
        if (!cleanIsbn) {
            if (onError) onError();
            return;
        }

        // Try OpenLibrary first
        img.src = `https://covers.openlibrary.org/b/isbn/${cleanIsbn}-L.jpg`;
    }
}

// Initialize on page load
const bookCoverManager = new BookCoverManager();

// Export for use in other scripts
if (typeof window !== 'undefined') {
    window.BookCoverManager = BookCoverManager;
    window.bookCoverManager = bookCoverManager;
}

