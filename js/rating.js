/**
 * CampusNexus — Star Rating Component
 * Interactive star ratings with AJAX
 */
function initStarRating(containerId, callback) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const stars = container.querySelectorAll('i[data-rating]');
    
    stars.forEach(star => {
        star.addEventListener('mouseenter', () => {
            const rating = parseInt(star.dataset.rating);
            stars.forEach((s, i) => {
                s.style.color = (i < rating) ? 'var(--warning)' : 'var(--text-muted)';
            });
        });
        
        star.addEventListener('click', () => {
            const rating = parseInt(star.dataset.rating);
            stars.forEach((s, i) => {
                s.classList.toggle('active', i < rating);
            });
            if (callback) callback(rating);
        });
    });
    
    container.addEventListener('mouseleave', () => {
        stars.forEach(s => {
            s.style.color = s.classList.contains('active') ? 'var(--warning)' : 'var(--text-muted)';
        });
    });
}
