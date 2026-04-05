/**
 * CampusNexus — Real-time Filter
 * Client-side filtering for cards without page reload
 */
function initClientFilter(searchInputId, filterSelects, cardSelector) {
    const searchInput = document.getElementById(searchInputId);
    const cards = document.querySelectorAll(cardSelector);
    
    function filterCards() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const filters = {};
        filterSelects.forEach(sel => {
            const el = document.getElementById(sel.id);
            if (el) filters[sel.attr] = el.value.toLowerCase();
        });
        
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            const matchesSearch = !searchTerm || text.includes(searchTerm);
            let matchesFilters = true;
            
            Object.keys(filters).forEach(attr => {
                if (filters[attr]) {
                    const cardVal = card.dataset[attr] || '';
                    if (cardVal.toLowerCase() !== filters[attr]) matchesFilters = false;
                }
            });
            
            card.style.display = (matchesSearch && matchesFilters) ? '' : 'none';
        });
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', filterCards);
    }
    filterSelects.forEach(sel => {
        const el = document.getElementById(sel.id);
        if (el) el.addEventListener('change', filterCards);
    });
}
