/**
 * CampusNexus — Form Validation Library
 */

class FormValidator {
    constructor(formId, rules) {
        this.form = document.getElementById(formId);
        this.rules = rules;
        this.errors = {};
        if (this.form) this.init();
    }

    init() {
        this.form.addEventListener('submit', (e) => {
            if (!this.validate()) { e.preventDefault(); }
        });
        // Live validation on blur
        Object.keys(this.rules).forEach(field => {
            const input = this.form.querySelector(`[name="${field}"]`);
            if (input) {
                input.addEventListener('blur', () => this.validateField(field));
                input.addEventListener('input', () => {
                    if (this.errors[field]) this.validateField(field);
                });
            }
        });
    }

    validate() {
        this.errors = {};
        Object.keys(this.rules).forEach(field => this.validateField(field));
        return Object.keys(this.errors).length === 0;
    }

    validateField(field) {
        const input = this.form.querySelector(`[name="${field}"]`);
        if (!input) return;
        const value = input.value.trim();
        const fieldRules = this.rules[field];
        delete this.errors[field];

        if (fieldRules.required && !value) {
            this.errors[field] = fieldRules.requiredMsg || `${fieldRules.label || field} is required`;
        } else if (value) {
            if (fieldRules.minLength && value.length < fieldRules.minLength) {
                this.errors[field] = `${fieldRules.label || field} must be at least ${fieldRules.minLength} characters`;
            }
            if (fieldRules.maxLength && value.length > fieldRules.maxLength) {
                this.errors[field] = `${fieldRules.label || field} must be less than ${fieldRules.maxLength} characters`;
            }
            if (fieldRules.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                this.errors[field] = 'Please enter a valid email address';
            }
            if (fieldRules.match) {
                const matchInput = this.form.querySelector(`[name="${fieldRules.match}"]`);
                if (matchInput && value !== matchInput.value.trim()) {
                    this.errors[field] = fieldRules.matchMsg || 'Fields do not match';
                }
            }
            if (fieldRules.pattern && !fieldRules.pattern.test(value)) {
                this.errors[field] = fieldRules.patternMsg || `Invalid ${fieldRules.label || field}`;
            }
        }
        this.showFieldError(field, input);
    }

    showFieldError(field, input) {
        const errorEl = document.getElementById(`${field}-error`) || input.parentElement.querySelector('.form-error');
        if (this.errors[field]) {
            input.classList.add('error');
            if (errorEl) { errorEl.textContent = this.errors[field]; errorEl.classList.add('visible'); }
        } else {
            input.classList.remove('error');
            if (errorEl) { errorEl.textContent = ''; errorEl.classList.remove('visible'); }
        }
    }
}

/* Password Strength Meter */
function initPasswordStrength(inputId) {
    const input = document.getElementById(inputId);
    const bar = document.querySelector('.password-strength-bar');
    const text = document.querySelector('.password-strength-text');
    if (!input || !bar) return;

    input.addEventListener('input', () => {
        const val = input.value;
        let score = 0;
        if (val.length >= 6) score++;
        if (val.length >= 10) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
            { width: '0%', color: 'transparent', label: '' },
            { width: '20%', color: '#EF4444', label: 'Very Weak' },
            { width: '40%', color: '#F59E0B', label: 'Weak' },
            { width: '60%', color: '#F59E0B', label: 'Fair' },
            { width: '80%', color: '#22C55E', label: 'Strong' },
            { width: '100%', color: '#22C55E', label: 'Very Strong' },
        ];
        const level = levels[Math.min(score, 5)];
        bar.style.width = level.width;
        bar.style.background = level.color;
        if (text) { text.textContent = level.label; text.style.color = level.color; }
    });
}

/* Character Counter */
function initCharCounter(inputId, counterId, maxChars) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    if (!input || !counter) return;
    input.addEventListener('input', () => {
        const remaining = maxChars - input.value.length;
        counter.textContent = `${input.value.length}/${maxChars}`;
        counter.style.color = remaining < 20 ? 'var(--danger)' : 'var(--text-muted)';
    });
}
