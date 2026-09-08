
let simulationIntervalId = null;

document.addEventListener('DOMContentLoaded', async function () {
    const isLoggedin = localStorage.getItem('isLoggedIn');
    if (isLoggedin !== 'true') {
        window.location.href = 'index.html';
        return;
    }

    const username = localStorage.getItem('user') || 'User';
    updateGreeting(username);

    const userNameSpan = document.getElementById('userName');
    if (userNameSpan) {
        userNameSpan.textContent = username;
    }

    updateStatistics();
    populateActivityTable();
    setupLogout();

    showInventoryLoading(true);
    try {
        await DataManager.initializeData();
    } catch (err) {
        console.error(err);
        showInventoryLoading(false);
        showToast('Could not load inventory data — check the console.');
        return;
    }
    showInventoryLoading(false);

    renderInventoryDashboard();
    setupFilterControls();
    setupSearch();
    setupExport();
    startRealTimeSimulation();
});


function updateGreeting(username) {
    const greetingElement = document.getElementById('greeting');
    if (!greetingElement) return;

    const hour = new Date().getHours();
    let timeOfDay = '';

    if (hour >= 5 && hour < 12) {
        timeOfDay = 'Good Morning';
    } else if (hour >= 12 && hour < 17) {
        timeOfDay = 'Good Afternoon';
    } else if (hour >= 17 && hour < 21) {
        timeOfDay = 'Good Evening';
    } else {
        timeOfDay = 'Good Night';
    }

    greetingElement.textContent = `${timeOfDay}, ${username}!`;
}

function updateStatistics() {
    const stats = [
        { title: "Today's Orders", value: '32', color: 'text-primary', icon: '🍽' },
        { title: "Today's Revenue", value: '$19,150', color: 'text-success', icon: '💰' },
        { title: 'Reservation', value: '12', color: 'text-info', icon: '📅' },
        { title: 'Available Tables', value: '8', color: 'text-warning', icon: '🪑' }
    ];

    stats.forEach((stat, index) => {
        const titleElement = document.getElementById(`stat${index + 1}-title`);
        const valueElement = document.getElementById(`stat${index + 1}-value`);

        if (titleElement) {
            titleElement.textContent = `${stat.icon} ${stat.title}`;
        }
        if (valueElement) {
            valueElement.textContent = stat.value;
            valueElement.className = `card-text fw-bold ${stat.color}`;
        }
    });
}

function populateActivityTable() {
    const tableBody = document.getElementById('activityTableBody');
    if (!tableBody) return;

    const activities = [
        { date: '2026-08-10 14:30', activity: 'New reservation received for Table 8', status: 'success' },
        { date: '2026-08-10 13:15', activity: 'Menu item "Beef Steak" updated', status: 'info' },
        { date: '2026-08-10 11:45', activity: 'Low inventory alert: Chicken Breast', status: 'warning' },
        { date: '2026-08-10 09:00', activity: 'New Customer Reservation Up', status: 'success' },
        { date: '2026-08-09 16:20', activity: 'Inventory restocked: Soft Drinks', status: 'success' },
        { date: '2026-08-09 14:10', activity: 'Table 5 reservation has been cancelled', status: 'danger' }
    ];

    tableBody.innerHTML = '';

    activities.forEach((activity) => {
        const row = document.createElement('tr');

        let badgeClass = 'bg-secondary';
        if (activity.status === 'success') badgeClass = 'bg-success';
        else if (activity.status === 'warning') badgeClass = 'bg-warning text-dark';
        else if (activity.status === 'danger') badgeClass = 'bg-danger';
        else if (activity.status === 'info') badgeClass = 'bg-info text-dark';

        row.innerHTML = `
            <td>${activity.date}</td>
            <td>${activity.activity}</td>
            <td><span class="badge ${badgeClass}">${activity.status}</span></td>
        `;

        tableBody.appendChild(row);
    });
}

function setupLogout() {
    const logoutBtn = document.getElementById('logoutBtn');
    const logoutLink = document.getElementById('logoutLink');

    function performLogout(e) {
        e.preventDefault();
        if (simulationIntervalId) clearInterval(simulationIntervalId);
        localStorage.removeItem('isLoggedIn');
        localStorage.removeItem('user');
        window.location.href = 'index.html';
    }

    if (logoutBtn) logoutBtn.addEventListener('click', performLogout);
    if (logoutLink) logoutLink.addEventListener('click', performLogout);
}

function showInventoryLoading(isLoading) {
    const spinner = document.getElementById('inventoryLoading');
    const content = document.getElementById('inventoryContent');
    if (spinner) spinner.classList.toggle('d-none', !isLoading);
    if (content) content.classList.toggle('d-none', isLoading);
}

function renderInventoryDashboard() {
    const products = DataManager.applyFilters();
    const searchInput = document.getElementById('productSearch');
    renderInventoryTable(products, searchInput ? searchInput.value : '');
    renderLowStockAlerts();
    renderCharts(); // defined in charts.js, pulls from DataManager itself
    updateInventorySummary();
}

function refreshInventoryView() {
    const products = DataManager.applyFilters();
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
        const status = DataManager.getStockStatus(product);
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

    const lowStock = [...DataManager.getLowStockProducts()].sort((a, b) => a.quantity - b.quantity);

    if (lowStock.length === 0) {
        alertContainer.innerHTML = '';
        return;
    }

    const items = lowStock
        .slice(0, 6)
        .map((p) => {
            const status = DataManager.getStockStatus(p);
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
    const stats = DataManager.getStockStatistics();
    const setText = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    };
    setText('invTotalProducts', stats.totalProducts);
    setText('invTotalValue', `$${stats.totalValue.toFixed(2)}`);
    setText('invLowStock', stats.lowStockCount);
    setText('invOutOfStock', stats.outOfStockCount);
}


function setupFilterControls() {
    const categorySelect = document.getElementById('categoryFilter');
    const stockButtons = document.querySelectorAll('[data-stock-filter]');
    const minPriceInput = document.getElementById('minPriceFilter');
    const maxPriceInput = document.getElementById('maxPriceFilter');
    const applyBtn = document.getElementById('applyFiltersBtn');
    const resetBtn = document.getElementById('resetFiltersBtn');

    if (categorySelect) {
        const categories = [...new Set(DataManager.getProducts().map((p) => p.category))].sort();
        categorySelect.innerHTML =
            '<option value="all">All Categories</option>' +
            categories.map((c) => `<option value="${c}">${c}</option>`).join('');

        categorySelect.addEventListener('change', () => {
            DataManager.filterByCategory(categorySelect.value);
            refreshInventoryView();
        });
    }

    stockButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            stockButtons.forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            DataManager.filterByStockStatus(btn.dataset.stockFilter);
            refreshInventoryView();
        });
    });

    if (applyBtn) {
        applyBtn.addEventListener('click', () => {
            const min = minPriceInput && minPriceInput.value !== '' ? parseFloat(minPriceInput.value) : null;
            const max = maxPriceInput && maxPriceInput.value !== '' ? parseFloat(maxPriceInput.value) : null;
            DataManager.filterByPriceRange(min, max);
            refreshInventoryView();
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            DataManager.resetFilters();
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
            DataManager.updateSearchResults(searchInput.value);
            refreshInventoryView();
        }, 250);
    });
}

function setupExport() {
    const exportBtn = document.getElementById('exportCsvBtn');
    if (!exportBtn) return;

    exportBtn.addEventListener('click', () => {
        const products = DataManager.applyFilters();
        const csv = DataManager.exportToCSV(products);
        const filename = `inventory_export_${new Date().toISOString().slice(0, 10)}.csv`;
        DataManager.downloadCSV(csv, filename);
    });
}


function startRealTimeSimulation() {
    if (simulationIntervalId) clearInterval(simulationIntervalId);

    simulationIntervalId = setInterval(() => {
        const change = DataManager.simulateInventoryChange();
        if (!change) return;

        const direction = change.delta >= 0 ? '+' : '';
        showToast(`${change.product.name}: ${direction}${change.delta} units → now ${change.product.quantity}`);

        renderInventoryDashboard();
    }, DataManager.CONFIG.simulationIntervalMs);
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