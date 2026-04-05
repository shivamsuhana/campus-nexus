/**
 * CampusNexus — Attendance JS
 * Timer countdown and code input handling
 */
document.addEventListener('DOMContentLoaded', () => {
    initTimers();
});

function initTimers() {
    document.querySelectorAll('.attendance-timer').forEach(el => {
        const endTime = new Date(el.dataset.end).getTime();
        const textEl = el.querySelector('.timer-text');
        
        function update() {
            const now = Date.now();
            const remaining = endTime - now;
            if (remaining <= 0) {
                textEl.textContent = 'Expired';
                el.style.color = 'var(--danger)';
                return;
            }
            const minutes = Math.floor(remaining / 60000);
            const seconds = Math.floor((remaining % 60000) / 1000);
            textEl.textContent = `${String(minutes).padStart(2,'0')}:${String(seconds).padStart(2,'0')} remaining`;
            requestAnimationFrame(update);
        }
        update();
    });
}

function handleCodeInput(input) {
    const val = input.value.replace(/\D/g, '');
    input.value = val;
    if (val && input.dataset.index < 5) {
        const next = document.querySelector(`.code-digit[data-index="${parseInt(input.dataset.index) + 1}"]`);
        if (next) next.focus();
    }
    updateSessionCode();
}

function handleCodeKeydown(e, input) {
    if (e.key === 'Backspace' && !input.value && input.dataset.index > 0) {
        const prev = document.querySelector(`.code-digit[data-index="${parseInt(input.dataset.index) - 1}"]`);
        if (prev) { prev.focus(); prev.value = ''; }
    }
}

function updateSessionCode() {
    const digits = document.querySelectorAll('.code-digit');
    let code = '';
    digits.forEach(d => code += d.value);
    const hidden = document.getElementById('sessionCode');
    if (hidden) hidden.value = code;
}
