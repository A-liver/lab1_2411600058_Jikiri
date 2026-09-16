let categoryChart = null;
let stockStatusChart = null;
let topProductsChart = null;
 
const THEME_COLORS = ['#606C38', '#BC6C25', '#DDA15E', '#283618', '#8a9a5b', '#a3b18a', '#3a5a40'];
 
function renderCharts() {
    renderCategoryChart(DataManager.getProducts());
    renderStockStatusChart(DataManager.getStockStatistics());
    renderTopProductsChart(DataManager.getTopProductsByValue(10));
}

function buildCategoryStockBreakdown(products) {
    const byCategory = {};
    products.forEach((p) => {
        if (!byCategory[p.category]) {
            byCategory[p.category] = { 'in-stock': 0, 'low-stock': 0, 'out-of-stock': 0 };
        }
        const status = DataManager.getStockStatus(p);
        byCategory[p.category][status] += p.quantity * p.unitPrice;
    });

    const categories = Object.keys(byCategory);
    return {
        categories,
        inStock: categories.map((c) => Number(byCategory[c]['in-stock'].toFixed(2))),
        lowStock: categories.map((c) => Number(byCategory[c]['low-stock'].toFixed(2))),
        outOfStock: categories.map((c) => Number(byCategory[c]['out-of-stock'].toFixed(2)))
    };
}

function renderCategoryChart(products) {
    const canvas = document.getElementById('categoryValueChart');
    if (!canvas || typeof Chart === 'undefined') return;

    if (categoryChart) categoryChart.destroy();

    const breakdown = buildCategoryStockBreakdown(products);

    categoryChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: breakdown.categories,
            datasets: [
                {
                    label: 'In Stock ($)',
                    data: breakdown.inStock,
                    backgroundColor: '#28a745'
                },
                {
                    label: 'Low Stock ($)',
                    data: breakdown.lowStock,
                    backgroundColor: '#f3a712'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                title: { display: true, text: 'Inventory Value by Category' }
            },
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true }
            }
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
            },
            scales: {
                x: { stacked: true },
                y: { stacked: true }
            }
        }
    });
}