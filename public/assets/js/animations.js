/**
 * Luminara Library — Animations
 * Butterflies, floating books, scroll reveal, glow particles
 */

const Animations = (() => {

    /* ── Butterfly Factory ─────────────────────── */
    function createButterflies(container, count = 8) {
        const colors = [
            'hsl(270, 80%, 65%)', // purple
            'hsl(190, 90%, 55%)', // cyan
            'hsl(320, 70%, 60%)', // pink
            'hsl(45, 90%, 60%)',  // gold
        ];

        for (let i = 0; i < count; i++) {
            const butterfly = document.createElement('div');
            butterfly.className = 'butterfly';
            butterfly.setAttribute('aria-hidden', 'true');

            const color = colors[i % colors.length];
            const size = 18 + Math.random() * 16;

            butterfly.innerHTML = `
                <svg class="butterfly-svg" width="${size}" height="${size}" viewBox="0 0 40 40" fill="none">
                    <g class="wing-left">
                        <ellipse cx="13" cy="16" rx="11" ry="8" fill="${color}" opacity="0.7" transform="rotate(-20 13 16)"/>
                        <ellipse cx="10" cy="26" rx="7" ry="5" fill="${color}" opacity="0.5" transform="rotate(-10 10 26)"/>
                    </g>
                    <g class="wing-right">
                        <ellipse cx="27" cy="16" rx="11" ry="8" fill="${color}" opacity="0.7" transform="rotate(20 27 16)"/>
                        <ellipse cx="30" cy="26" rx="7" ry="5" fill="${color}" opacity="0.5" transform="rotate(10 30 26)"/>
                    </g>
                    <ellipse cx="20" cy="20" rx="1.5" ry="10" fill="${color}" opacity="0.9"/>
                </svg>
            `;

            butterfly.style.left = (5 + Math.random() * 90) + '%';
            butterfly.style.top = (10 + Math.random() * 80) + '%';
            butterfly.style.animationDuration = (15 + Math.random() * 20) + 's';
            butterfly.style.animationDelay = (Math.random() * 10) + 's';
            butterfly.style.opacity = 0.3 + Math.random() * 0.35;

            // Vary the animation
            const xDrift = -100 + Math.random() * 200;
            const yDrift = -150 + Math.random() * 100;
            butterfly.style.setProperty('--drift-x', xDrift + 'px');
            butterfly.style.setProperty('--drift-y', yDrift + 'px');

            container.appendChild(butterfly);

            // Add subtle random drift via JS
            animateButterfly(butterfly);
        }
    }

    function animateButterfly(el) {
        let x = 0, y = 0, angle = Math.random() * Math.PI * 2;
        const speed = 0.2 + Math.random() * 0.3;
        const amplitude = 30 + Math.random() * 50;

        function step() {
            angle += speed * 0.02;
            x = Math.sin(angle * 1.3) * amplitude;
            y = Math.cos(angle * 0.8) * amplitude * 0.6;
            el.style.transform = `translate(${x}px, ${y}px) rotate(${Math.sin(angle) * 15}deg)`;
            requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    /* ── Floating Books Factory ────────────────── */
    function createFloatingBooks(container, count = 6) {
        const bookEmojis = ['📖', '📚', '📕', '📗', '📘', '📙', '📓', '📒'];

        for (let i = 0; i < count; i++) {
            const book = document.createElement('div');
            book.className = 'floating-book';
            book.setAttribute('aria-hidden', 'true');
            book.textContent = bookEmojis[i % bookEmojis.length];

            book.style.left = (8 + Math.random() * 84) + '%';
            book.style.top = (15 + Math.random() * 70) + '%';
            book.style.fontSize = (1.2 + Math.random() * 1) + 'rem';
            book.style.animationDuration = (18 + Math.random() * 15) + 's';
            book.style.animationDelay = (Math.random() * 8) + 's';
            book.style.opacity = 0.12 + Math.random() * 0.15;

            container.appendChild(book);
        }
    }

    /* ── Scroll Reveal ─────────────────────────── */
    function initScrollReveal() {
        const elements = document.querySelectorAll('.reveal-on-scroll');
        if (!elements.length) return;

        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        elements.forEach(el => observer.observe(el));
    }

    /* ── Glow Follow Cursor (Hero) ─────────────── */
    function initCursorGlow() {
        const hero = document.querySelector('.hero');
        if (!hero) return;

        const glow = document.createElement('div');
        glow.style.cssText = `
            position: absolute; width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(124,58,237,0.12) 0%, transparent 70%);
            pointer-events: none; z-index: 1;
            transition: transform 0.3s ease-out, opacity 0.3s ease;
            opacity: 0;
        `;
        hero.style.position = 'relative';
        hero.appendChild(glow);

        hero.addEventListener('mousemove', e => {
            const rect = hero.getBoundingClientRect();
            const x = e.clientX - rect.left - 200;
            const y = e.clientY - rect.top - 200;
            glow.style.transform = `translate(${x}px, ${y}px)`;
            glow.style.opacity = '1';
        });

        hero.addEventListener('mouseleave', () => {
            glow.style.opacity = '0';
        });
    }

    /* ── Card Tilt Effect ──────────────────────── */
    function initCardTilt() {
        document.querySelectorAll('.book-card').forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const midX = rect.width / 2;
                const midY = rect.height / 2;
                const rotateX = ((y - midY) / midY) * -5;
                const rotateY = ((x - midX) / midX) * 5;

                card.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-6px)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });
    }

    /* ── Init ───────────────────────────────────── */
    function init() {
        const floatingContainer = document.querySelector('.floating-decorations');
        if (floatingContainer) {
            createButterflies(floatingContainer, 7);
            createFloatingBooks(floatingContainer, 5);
        }

        initScrollReveal();
        initCursorGlow();
        initCardTilt();
    }

    document.addEventListener('DOMContentLoaded', init);

    return { createButterflies, createFloatingBooks };
})();
