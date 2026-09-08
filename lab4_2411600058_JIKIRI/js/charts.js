
let categoryChart = null;
let stockStatusChart = null;
let topProductsChart = null;
 
const THEME_COLORS = ['#606C38', '#BC6C25', '#DDA15E', '#283618', '#8a9a5b', '#a3b18a', '#3a5a40'];
 
function renderCharts() {
    renderCategoryChart(DataManager.getCategorySummary());
    renderStockStatusChart(DataManager.getStockStatistics());
    renderTopProductsChart(DataManager.getTopProductsByValue(10));
}
 
function renderCategoryChart(summary) {
    const canvas = document.getElementById('categoryValueChart');
    if (!canvas || typeof Chart === 'undefined') return;
 
    if (categoryChart) categoryChart.destroy();
 
    categoryChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: summary.map((s) => s.category),
            datasets: [{
                label: 'Inventory Value ($)',
                data: summary.map((s) => Number(s.totalValue.toFixed(2))),
                backgroundColor: THEME_COLORS
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Inventory Value by Category' }
            },
            scales: { y: { beginAtZero: true } }
        }
    });
}
 
function renderStockStatusChart(stats) {
    const canvas = document.getElementById('stockStatusChart');
    if (!canvas || typeof Chart === 'undefined') return;
 
    const inStock = Math.max(0, stats.totalProducts - stats.lowStockCount - stats.outOfStockCount);
 
    if (stockStatusChart) stockStatusChart.destroy();
 
    stockStatusChart = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: ['In Stock', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: [inStock, stats.lowStockCount, stats.outOfStockCount],
                backgroundColor: ['#28a745', '#f3a712', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: 'Stock Status Distribution' }
            }
        }
    });
}
 
function renderTopProductsChart(products) {
    const canvas = document.getElementById('topProductsChart');
    if (!canvas || typeof Chart === 'undefined') return;
 
    if (topProductsChart) topProductsChart.destroy();
 
    topProductsChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: products.map((p) => p.name),
            datasets: [{
                label: 'Value ($)',
                data: products.map((p) => Number((p.quantity * p.unitPrice).toFixed(2))),
                backgroundColor: '#BC6C25'
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Top 10 Products by Value' }
            }
        }
    });
}