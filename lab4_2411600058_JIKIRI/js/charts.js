let categoryChart = null;
let stockStatusChart = null;
let topProductsChart = null;
let simulationIntervalId = null;


const CONFIG = {
    productsUrl: 'json/products.json',
    simulationIntervalMs: 8000,
    simulationMaxDelta: 5
};

let allProducts = [];
let currentFilters = {
    category: 'all',
    stock: 'all',
    minPrice: null,
    maxPrice: null
};
let currentSearchQuery = '';

async function loadProducts() {
    const res = await fetch(CONFIG.productsUrl);
    if (!res.ok) {
        throw new Error(`Failed to load ${CONFIG.productsUrl}: ${res.status}`);
    }
    allProducts = await res.json();
}

function getProducts() {
    return allProducts;
}

function getStockStatus(product) {
    if (product.quantity <= 0) return 'out-of-stock';
    if (product.quantity <= product.reorderLevel) return 'low-stock';
    return 'in-stock';
}

function filterByCategory(category) {
    currentFilters.category = category;
}

function filterByStockStatus(status) {
    currentFilters.stock = status;
}

function filterByPriceRange(min, max) {
    currentFilters.minPrice = min;
    currentFilters.maxPrice = max;
}

function resetFilters() {
    currentFilters = { category: 'all', stock: 'all', minPrice: null, maxPrice: null };
    currentSearchQuery = '';
}

function updateSearchResults(query) {
    currentSearchQuery = query || '';
}

function applyFilters() {
    let result = [...allProducts];

    if (currentFilters.category && currentFilters.category !== 'all') {
        result = result.filter((p) => p.category === currentFilters.category);
    }

    if (currentFilters.stock && currentFilters.stock !== 'all') {
        result = result.filter((p) => getStockStatus(p) === currentFilters.stock);
    }

    if (currentFilters.minPrice !== null && !Number.isNaN(currentFilters.minPrice)) {
        result = result.filter((p) => p.unitPrice >= currentFilters.minPrice);
    }
    if (currentFilters.maxPrice !== null && !Number.isNaN(currentFilters.maxPrice)) {
        result = result.filter((p) => p.unitPrice <= currentFilters.maxPrice);
    }

    if (currentSearchQuery && currentSearchQuery.trim()) {
        const q = currentSearchQuery.trim().toLowerCase();
        result = result.filter(
            (p) => p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q)
        );
    }

    return result;
}

function getLowStockProducts() {
    return allProducts
        .filter((p) => getStockStatus(p) !== 'in-stock')
        .sort((a, b) => a.quantity - b.quantity);
}

function getStockStatistics() {
    const totalProducts = allProducts.length;
    const totalValue = allProducts.reduce((sum, p) => sum + p.quantity * p.unitPrice, 0);
    const lowStockCount = allProducts.filter((p) => getStockStatus(p) === 'low-stock').length;
    const outOfStockCount = allProducts.filter((p) => getStockStatus(p) === 'out-of-stock').length;

    return { totalProducts, totalValue, lowStockCount, outOfStockCount };
}

function getCategorySummary() {
    const map = new Map();
    allProducts.forEach((p) => {
        const entry = map.get(p.category) || { category: p.category, totalValue: 0, count: 0 };
        entry.totalValue += p.quantity * p.unitPrice;
        entry.count += 1;
        map.set(p.category, entry);
    });
    return [...map.values()].sort((a, b) => b.totalValue - a.totalValue);
}

function getTopProductsByValue(n = 5) {
    return [...allProducts]
        .sort((a, b) => b.quantity * b.unitPrice - a.quantity * a.unitPrice)
        .slice(0, n);
}

function exportToCSV(products) {
    const header = ['SKU', 'Name', 'Category', 'Quantity', 'Unit Price', 'Total Value', 'Status', 'Supplier', 'Last Updated'];
    const rows = products.map((p) => [
        p.sku,
        p.name,
        p.category,
        p.quantity,
        p.unitPrice.toFixed(2),
        (p.quantity * p.unitPrice).toFixed(2),
        getStockStatus(p),
        p.supplier,
        p.lastUpdated
    ]);

    const escapeCell = (cell) => {
        const str = String(cell);
        return /[",\n]/.test(str) ? `"${str.replace(/"/g, '""')}"` : str;
    };

    return [header, ...rows].map((row) => row.map(escapeCell).join(',')).join('\n');
}

function downloadCSV(csv, filename) {
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}


function simulateInventoryChange() {
    if (allProducts.length === 0) return null;

    const product = allProducts[Math.floor(Math.random() * allProducts.length)];
    const delta = Math.floor(Math.random() * (CONFIG.simulationMaxDelta * 2 + 1)) - CONFIG.simulationMaxDelta;
    if (delta === 0) return null;

    product.quantity = Math.max(0, product.quantity + delta);
    product.lastUpdated = new Date().toISOString().slice(0, 10);

    return { product, delta };
}
document.addEventListener('DOMContentLoaded', async function () {

    showInventoryLoading(true);
    try {
        await loadProducts();
    } catch (err) {
        console.error(err);
        showInventoryLoading(false);
        showToast('Could not load products.json — check the console.');
        return;
    }
    showInventoryLoading(false);

    renderInventoryDashboard();
    setupFilterControls();
    setupSearch();
    setupExport();
    startRealTimeSimulation();
});

function showInventoryLoading(isLoading) {
    const spinner = document.getElementById('inventoryLoading');
    const content = document.getElementById('inventoryContent');
    if (spinner) spinner.classList.toggle('d-none', !isLoading);
    if (content) content.classList.toggle('d-none', isLoading);
}

function renderInventoryDashboard() {
    const products = applyFilters();
    const searchInput = document.getElementById('productSearch');
    renderInventoryTable(products, searchInput ? searchInput.value : '');
    renderLowStockAlerts();
    renderCharts();
    updateInventorySummary();
}

function refreshInventoryView() {
    const products = applyFilters();
    const searchInput = document.getElementById('productSearch');
    renderInventoryTable(products, searchInput ? searchInput.value : '');
    updateInventorySummary();
}

function renderInventoryTable(products, query = '') {
    const tableBody = document.getElementById('inventoryTableBody');
    const emptyState = document.getElementById('inventoryEmptyState');
    if (!tableBody) return;

    tableBody.innerHTML = '';

    if (products.length === 0) {
        if (emptyState) emptyState.classList.remove('d-none');
        return;
    }
    if (emptyState) emptyState.classList.add('d-none');

    const fragment = document.createDocumentFragment();

    products.forEach((product) => {
        const status = getStockStatus(product);
        const row = document.createElement('tr');
        row.dataset.productId = product.id;

        if (status === 'low-stock') row.classList.add('row-low-stock');
        if (status === 'out-of-stock') row.classList.add('row-out-of-stock');

        let badgeClass = 'bg-success';
        let badgeText = 'In Stock';
        if (status === 'low-stock') { badgeClass = 'bg-warning text-dark'; badgeText = 'Low Stock'; }
        if (status === 'out-of-stock') { badgeClass = 'bg-danger'; badgeText = 'Out of Stock'; }

        const totalValue = (product.quantity * product.unitPrice).toFixed(2);

        row.innerHTML = `
            <td>${highlightMatch(product.sku, query)}</td>
            <td>${highlightMatch(product.name, query)}</td>
            <td>${product.category}</td>
            <td>${product.quantity}</td>
            <td>$${product.unitPrice.toFixed(2)}</td>
            <td>$${totalValue}</td>


            <td><span class="badge ${badgeClass}">${badgeText}</span></td>

            <td>${product.supplier}</td>
            <td>${product.lastUpdated}</td>

        `;

        fragment.appendChild(row);
    });

    tableBody.appendChild(fragment);
}

function highlightMatch(text, query) {
    const escaped = String(text).replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));

    if (!query || !query.trim()) return escaped;

    const escapedQuery = query.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(`(${escapedQuery})`, 'ig');
    return escaped.replace(regex, '<mark>$1</mark>');
}

function renderLowStockAlerts() {
    const alertContainer = document.getElementById('lowStockAlert');
    if (!alertContainer) return;

    const lowStock = getLowStockProducts();

    if (lowStock.length === 0) {
        alertContainer.innerHTML = '';
        return;
    }

    const items = lowStock
        .slice(0, 6)
        .map((p) => {
            const status = getStockStatus(p);
            const label = status === 'out-of-stock' ? 'OUT OF STOCK' : `${p.quantity} left (reorder at ${p.reorderLevel})`;
            return `<li><strong>${p.name}</strong> — ${label}</li>`;
        })
        .join('');

    alertContainer.innerHTML = `
        <div class="alert alert-warning alert-dismissible fade show d-flex" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 flex-shrink-0"></i>
            <div>
                <strong>${lowStock.length} item(s) need attention</strong>
                <ul class="mb-0 mt-2 small">${items}</ul>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
}

function updateInventorySummary() {
    const stats = getStockStatistics();
    const setText = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    };
    setText('invTotalProducts', stats.totalProducts);
    setText('invTotalValue', `$${stats.totalValue.toFixed(2)}`);
    setText('invLowStock', stats.lowStockCount);
    setText('invOutOfStock', stats.outOfStockCount);
}


function renderCharts() {
    renderCategoryChart(getCategorySummary());
    renderStockStatusChart(getStockStatistics());
    renderTopProductsChart(getTopProductsByValue(10));
}

const THEME_COLORS = ['#606C38', '#BC6C25', '#DDA15E', '#283618', '#8a9a5b', '#a3b18a', '#3a5a40'];

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

function setupFilterControls() {
    const categorySelect = document.getElementById('categoryFilter');
    const stockButtons = document.querySelectorAll('[data-stock-filter]');
    const minPriceInput = document.getElementById('minPriceFilter');
    const maxPriceInput = document.getElementById('maxPriceFilter');
    const applyBtn = document.getElementById('applyFiltersBtn');
    const resetBtn = document.getElementById('resetFiltersBtn');

    if (categorySelect) {
        const categories = [...new Set(getProducts().map((p) => p.category))].sort();
        categorySelect.innerHTML =
            '<option value="all">All Categories</option>' +
            categories.map((c) => `<option value="${c}">${c}</option>`).join('');

        categorySelect.addEventListener('change', () => {
            filterByCategory(categorySelect.value);
            refreshInventoryView();
        });
    }

    stockButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            stockButtons.forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            filterByStockStatus(btn.dataset.stockFilter);
            refreshInventoryView();
        });
    });

    if (applyBtn) {
        applyBtn.addEventListener('click', () => {
            const min = minPriceInput && minPriceInput.value !== '' ? parseFloat(minPriceInput.value) : null;
            const max = maxPriceInput && maxPriceInput.value !== '' ? parseFloat(maxPriceInput.value) : null;
            filterByPriceRange(min, max);
            refreshInventoryView();
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            resetFilters();
            if (categorySelect) categorySelect.value = 'all';
            stockButtons.forEach((b) => b.classList.remove('active'));
            const allBtn = document.querySelector('[data-stock-filter="all"]');
            if (allBtn) allBtn.classList.add('active');
            if (minPriceInput) minPriceInput.value = '';
            if (maxPriceInput) maxPriceInput.value = '';
            const searchInput = document.getElementById('productSearch');
            if (searchInput) searchInput.value = '';
            refreshInventoryView();
        });
    }
}

function setupSearch() {
    const searchInput = document.getElementById('productSearch');
    if (!searchInput) return;

    let debounceTimer;
    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            updateSearchResults(searchInput.value);
            refreshInventoryView();
        }, 250);
    });
}

function setupExport() {
    const exportBtn = document.getElementById('exportCsvBtn');
    if (!exportBtn) return;

    exportBtn.addEventListener('click', () => {
        const products = applyFilters();
        const csv = exportToCSV(products);
        const filename = `inventory_export_${new Date().toISOString().slice(0, 10)}.csv`;
        downloadCSV(csv, filename);
    });
}

function startRealTimeSimulation() {
    if (simulationIntervalId) clearInterval(simulationIntervalId);

    simulationIntervalId = setInterval(() => {
        const change = simulateInventoryChange();
        if (!change) return;

        const direction = change.delta >= 0 ? '+' : '';
        showToast(`${change.product.name}: ${direction}${change.delta} units → now ${change.product.quantity}`);

        renderInventoryDashboard();
    }, CONFIG.simulationIntervalMs);
}

function showToast(message) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = 1080;
        document.body.appendChild(container);
    }

    const toastEl = document.createElement('div');
    toastEl.className = 'toast align-items-center text-bg-primary border-0';
    toastEl.setAttribute('role', 'alert');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body"><i class="bi bi-arrow-repeat me-2"></i>${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    container.appendChild(toastEl);

    if (typeof bootstrap !== 'undefined') {
        const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    } else {
        setTimeout(() => toastEl.remove(), 5000);
    }
}