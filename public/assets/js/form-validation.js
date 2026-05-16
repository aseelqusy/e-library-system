/**
 * Luminara Library — Form Validation
 * Real-time validation, password strength, submit handling
 */

const FormValidation = (() => {

    function init() {
        initPasswordStrength();
        initPasswordToggle();
        initRealTimeValidation();
        initFormSubmitValidation();
    }

    /* ── Password Strength Meter ───────────────── */
    function initPasswordStrength() {
        const input = document.getElementById('password');
        const meter = document.querySelector('.password-strength');
        const label = document.querySelector('.strength-text');
        if (!input || !meter) return;

        input.addEventListener('input', () => {
            const val = input.value;
            const score = calcStrength(val);
            const levels = ['', 'weak', 'fair', 'good', 'strong'];
            const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
            const colors = ['', '#ef4444', '#f59e0b', '#3b82f6', '#10b981'];

            meter.className = 'password-strength ' + (levels[score] || '');
            if (label) {
                label.textContent = val.length ? labels[score] || '' : '';
                label.style.color = colors[score] || '';
            }
        });
    }

    function calcStrength(password) {
        if (!password) return 0;
        let score = 0;
        if (password.length >= 6) score++;
        if (password.length >= 10) score++;
        if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        return Math.min(4, score);
    }

    /* ── Password Visibility Toggle ────────────── */
    function initPasswordToggle() {
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.closest('.input-group')?.querySelector('input');
                if (!input) return;

                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                btn.textContent = isPassword ? '🙈' : '👁';
                btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        });
    }

    /* ── Real-time Field Validation ────────────── */
    function initRealTimeValidation() {
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.querySelectorAll('input, textarea').forEach(input => {
                input.addEventListener('blur', () => validateField(input));
                input.addEventListener('input', () => {
                    if (input.classList.contains('error')) {
                        validateField(input);
                    }
                });
            });
        });
    }

    function validateField(input) {
        const rules = input.dataset.rules;
        if (!rules) return true;

        const ruleList = rules.split('|');
        let valid = true;
        let message = '';

        const label = input.dataset.label || input.placeholder || input.name;

        for (const rule of ruleList) {
            if (rule === 'required' && !input.value.trim()) {
                message = `${label} is required`;
                valid = false;
                break;
            }
            if (rule === 'email' && input.value && !isValidEmail(input.value)) {
                message = 'Please enter a valid email';
                valid = false;
                break;
            }
            if (rule.startsWith('min:')) {
                const min = parseInt(rule.split(':')[1]);
                if (input.value && input.value.length < min) {
                    message = `Must be at least ${min} characters`;
                    valid = false;
                    break;
                }
            }
            if (rule.startsWith('match:')) {
                const matchId = rule.split(':')[1];
                const matchInput = document.getElementById(matchId);
                if (matchInput && input.value !== matchInput.value) {
                    message = 'Passwords do not match';
                    valid = false;
                    break;
                }
            }
        }

        showFieldError(input, valid, message);
        return valid;
    }

    function showFieldError(input, valid, message) {
        input.classList.toggle('error', !valid);

        let errorEl = input.parentElement.querySelector('.form-error');
        if (!valid) {
            if (!errorEl) {
                errorEl = document.createElement('div');
                errorEl.className = 'form-error';
                input.parentElement.appendChild(errorEl);
            }
            errorEl.textContent = message;
        } else if (errorEl) {
            errorEl.remove();
        }
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    /* ── Form Submit Validation ────────────────── */
    function initFormSubmitValidation() {
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', e => {
                let allValid = true;
                form.querySelectorAll('input[data-rules], textarea[data-rules]').forEach(input => {
                    if (!validateField(input)) {
                        allValid = false;
                    }
                });

                if (!allValid) {
                    e.preventDefault();
                    const firstError = form.querySelector('.error');
                    if (firstError) firstError.focus();
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', init);

    return { validateField, calcStrength };
})();
