/**
 * CampusNexus — Chart.js Dashboard Integration
 */
document.addEventListener('DOMContentLoaded', () => {
    if (typeof categoryData !== 'undefined') initCategoryChart();
});

function initCategoryChart() {
    const ctx = document.getElementById('categoryChart');
    if (!ctx) return;

    const colors = {
        infrastructure: '#F5576C', it: '#4FACFE', hygiene: '#43E97B',
        safety: '#FEE140', electrical: '#F7971E', academic: '#A18CD1', other: '#30CFD0'
    };

    const labels = categoryData.map(d => d.category.charAt(0).toUpperCase() + d.category.slice(1));
    const data = categoryData.map(d => d.cnt);
    const bgColors = categoryData.map(d => colors[d.category] || '#667EEA');

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: bgColors,
                borderWidth: 0,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: getComputedStyle(document.documentElement).getPropertyValue('--text-secondary').trim() || '#A1A1AA',
                        padding: 16,
                        usePointStyle: true,
                        pointStyleWidth: 10,
                        font: { family: 'Inter', size: 12 }
                    }
                }
            }
        }
    });
}
