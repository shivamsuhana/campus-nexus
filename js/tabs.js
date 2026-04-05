/**
 * CampusNexus — Tab Switching
 */
function switchTab(tabName) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    
    event.target.classList.add('active');
    const target = document.getElementById('tab-' + tabName);
    if (target) target.classList.add('active');
}
