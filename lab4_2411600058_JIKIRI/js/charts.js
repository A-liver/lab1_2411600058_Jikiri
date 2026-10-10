let categoryCountChart = null;
let stockHealthChart = null;
let reorderChart = null;
let valueTrendChart = null;

const THEME_COLORS = ['#606C38', '#BC6C25', '#DDA15E', '#283618', '#8a9a5b', '#a3b18a', '#3a5a40'];

function renderCharts(products = DataManager.getProducts()) {
    const health = DataManager.getCategoryHealth(products);
    renderCategoryCountChart(health);
    renderValueTrendChart(DataManager.getValueHistory());
    renderReorderChart(DataManager.getReorderItems(products));
    renderStockHealthChart(health);
}

const STATUS_COLORS = { inStock: '#28a745', lowStock: '#f3a712', outOfStock: '#dc3545' };

function renderCategoryCountChart(health) {
    const canvas = document.getElementById('categoryCountChart');
    if (!canvas || typeof Chart === 'undefined') return;
    if (categoryCountChart) categoryCountChart.destroy();

    const total = health.reduce((sum, h) => sum + h.total, 0);

    categoryCountChart = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: health.map((h) => h.category),
            datasets: [{ data: health.map((h) => h.total), backgroundColor: THEME_COLORS }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                title: { display: true, text: `Products by Category` }
            }
        }
    });
}

function renderStockHealthChart(health) {
    const canvas = document.getElementById('stockHealthChart');
    if (!canvas || typeof Chart === 'undefined') return;
    if (stockHealthChart) stockHealthChart.destroy();

    const pct = (n, h) => (h.total ? Number(((n / h.total) * 100).toFixed(1)) : 0);

    stockHealthChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: health.map((h) => h.category),
            datasets: [
                { label: 'In Stock (%)', data: health.map((h) => pct(h.inStock, h)), backgroundColor: STATUS_COLORS.inStock },
                { label: 'Low Stock (%)', data: health.map((h) => pct(h.lowStock, h)), backgroundColor: STATUS_COLORS.lowStock },
                { label: 'Out of Stock (%)', data: health.map((h) => pct(h.outOfStock, h)), backgroundColor: STATUS_COLORS.outOfStock }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { title: { display: true, text: 'Stock Health by Category' } },
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true, max: 100 }
            }
        }
    });
}

function renderReorderChart(items) {
    const canvas = document.getElementById('reorderChart');
    if (!canvas || typeof Chart === 'undefined') return;
    if (reorderChart) reorderChart.destroy();

    const top = items.slice(0, 8);

    reorderChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: top.map((i) => i.name),
            datasets: [
                { label: 'Quantity', data: top.map((i) => i.quantity), backgroundColor: STATUS_COLORS.lowStock },
                { label: 'Reorder Level', data: top.map((i) => i.reorderLevel), backgroundColor: THEME_COLORS[0] }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { title: { display: true, text: 'Stock vs Reorder Level' } }
        }
    });
}

function renderValueTrendChart(history) {
    const canvas = document.getElementById('valueTrendChart');
    if (!canvas || typeof Chart === 'undefined') return;
    if (valueTrendChart) valueTrendChart.destroy();

    valueTrendChart = new Chart(canvas, {
        type: 'line',
        data: {
            labels: history.map((h) => h.time),
            datasets: [{
                label: 'Total Value ($)',
                data: history.map((h) => Number(h.value.toFixed(2))),
                borderColor: THEME_COLORS[1],
                backgroundColor: 'rgba(188, 108, 37, 0.15)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Total Inventory Value' }
            }
        }
    });
}