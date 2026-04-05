/**
 * CampusNexus — Modal System
 */
function openModal(modalId) {
    const overlay = document.getElementById('modalOverlay');
    const modal = document.getElementById(modalId);
    if (overlay) { overlay.classList.add('active'); }
    if (modal) { modal.classList.add('active'); }
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    const overlay = document.getElementById('modalOverlay');
    const modal = document.getElementById(modalId);
    if (overlay) overlay.classList.remove('active');
    if (modal) modal.classList.remove('active');
    document.body.style.overflow = '';
}

// Close on overlay click
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        document.querySelectorAll('.modal.active').forEach(m => {
            closeModal(m.id);
        });
    }
});

// Close on Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.active').forEach(m => {
            closeModal(m.id);
        });
    }
});
