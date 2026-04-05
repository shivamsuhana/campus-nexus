/**
 * CampusNexus — Core JavaScript
 * Navbar, Dark Mode, Notifications, Dropdowns, Smooth Scroll
 */

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initNavbar();
    initDropdowns();
    initScrollReveal();
    initFlashDismiss();
    initSmoothScroll();
    initCounters();
});

/* ============================================
   Theme Toggle (Dark/Light Mode)
   ============================================ */
function initThemeToggle() {
    const toggle = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');
    if (!toggle) return;

    // Check saved preference
    const saved = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', saved);
    updateThemeIcon(saved);

    // Also set cookie for PHP server-side if needed
    document.cookie = `theme=${saved};path=/;max-age=31536000`;

    toggle.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        document.cookie = `theme=${next};path=/;max-age=31536000`;
        updateThemeIcon(next);
    });

    function updateThemeIcon(theme) {
        if (icon) {
            icon.className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
        }
    }
}

/* ============================================
   Mobile Navbar
   ============================================ */
function initNavbar() {
    const toggleBtn = document.getElementById('navbarToggle');
    const nav = document.getElementById('navbarNav');
    if (!toggleBtn || !nav) return;

    toggleBtn.addEventListener('click', () => {
        toggleBtn.classList.toggle('active');
        nav.classList.toggle('active');
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
        if (!toggleBtn.contains(e.target) && !nav.contains(e.target)) {
            toggleBtn.classList.remove('active');
            nav.classList.remove('active');
        }
    });

    // Close on nav link click (mobile)
    nav.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            toggleBtn.classList.remove('active');
            nav.classList.remove('active');
        });
    });

    // Navbar scroll effect
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
        const navbar = document.getElementById('navbar');
        if (!navbar) return;
        const scrollY = window.scrollY;
        if (scrollY > 50) {
            navbar.style.boxShadow = 'var(--shadow-md)';
        } else {
            navbar.style.boxShadow = 'none';
        }
        lastScroll = scrollY;
    });
}

/* ============================================
   Dropdowns
   ============================================ */
function initDropdowns() {
    const userToggle = document.getElementById('userDropdownToggle');
    const userMenu = document.getElementById('userDropdownMenu');
    if (!userToggle || !userMenu) return;

    userToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        userMenu.classList.toggle('active');
    });

    document.addEventListener('click', (e) => {
        if (!userToggle.contains(e.target) && !userMenu.contains(e.target)) {
            userMenu.classList.remove('active');
        }
    });
}

/* ============================================
   Scroll Reveal Animation
   ============================================ */
function initScrollReveal() {
    const reveals = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
    if (!reveals.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    reveals.forEach(el => observer.observe(el));
}

/* ============================================
   Flash Message Auto-Dismiss
   ============================================ */
function initFlashDismiss() {
    const flash = document.getElementById('flashMessage');
    if (!flash) return;

    setTimeout(() => {
        flash.style.transition = 'opacity 0.5s, transform 0.5s';
        flash.style.opacity = '0';
        flash.style.transform = 'translateX(100%)';
        setTimeout(() => flash.remove(), 500);
    }, 5000);
}

/* ============================================
   Smooth Scroll for Anchor Links
   ============================================ */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

/* ============================================
   Utility: Show notification toast
   ============================================ */
function showNotification(message, type = 'info') {
    const existing = document.querySelector('.flash-message');
    if (existing) existing.remove();

    const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
    const div = document.createElement('div');
    div.className = `flash-message flash-${type}`;
    div.id = 'flashMessage';
    div.innerHTML = `
        <div class="flash-content">
            <i class="fas ${icons[type] || icons.info}"></i>
            <span>${message}</span>
        </div>
        <button class="flash-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    document.body.appendChild(div);

    setTimeout(() => {
        div.style.transition = 'opacity 0.5s, transform 0.5s';
        div.style.opacity = '0';
        div.style.transform = 'translateX(100%)';
        setTimeout(() => div.remove(), 500);
    }, 4000);
}

/* ============================================
   Utility: Sidebar toggle for mobile
   ============================================ */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) sidebar.classList.toggle('active');
    if (overlay) overlay.classList.toggle('active');
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) sidebar.classList.remove('active');
    if (overlay) overlay.classList.remove('active');
}

/* ============================================
   Utility: Confirm dialog
   ============================================ */
function confirmAction(message, callback) {
    if (confirm(message)) callback();
}

/* ============================================
   Animated Number Counters
   ============================================ */
function initCounters() {
    const counters = document.querySelectorAll('[data-count]');
    if (!counters.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.getAttribute('data-count')) || 0;
                const suffix = el.getAttribute('data-suffix') || '';
                animateCounter(el, 0, target, suffix, 1200);
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.3 });

    counters.forEach(el => observer.observe(el));
}

function animateCounter(el, start, end, suffix, duration) {
    const range = end - start;
    if (range === 0) { el.textContent = end + suffix; return; }
    const startTime = performance.now();

    function step(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        // Ease-out cubic
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(start + range * eased);
        el.textContent = current + suffix;
        if (progress < 1) {
            requestAnimationFrame(step);
        }
    }
    requestAnimationFrame(step);
}
