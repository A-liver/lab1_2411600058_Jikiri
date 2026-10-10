const DataManager = (function () {
    'use strict';

    const CONFIG = {
        apiEndpoint: 'api/products.php',
        localDataFile: 'json/products.json',
        simulationIntervalMs: 10000,
    };

    const state = {
        products: [],
        activeFilters: {
            category: 'all',
            stockStatus: 'all',
            minPrice: null,
            maxPrice: null
        },
        searchQuery: '',
        valueHistory: []
    };

    async function initializeData() {
        try {
            const response = await fetch(CONFIG.apiEndpoint, { cache: 'no-store' });
            if (!response.ok) throw new Error(`API responded with status ${response.status}`);
            const data = await response.json();
            if (!Array.isArray(data) || data.length === 0) throw new Error('API returned no data');
            state.products = data;
            console.info('DataManager: loaded products from live API.');
            return state.products;
        } catch (apiError) {
            console.warn('DataManager: live API unavailable, trying local JSON file.', apiError.message);
        }

        try {
            const response = await fetch(CONFIG.localDataFile, { cache: 'no-store' });
            if (!response.ok) throw new Error(`Could not load ${CONFIG.localDataFile} (status ${response.status})`);
            const data = await response.json();
            if (!Array.isArray(data) || data.length === 0) throw new Error('JSON file returned no data');
            state.products = data;
            console.info('DataManager: loaded products from local JSON file.');
            return state.products;
        } catch (jsonError) {
            console.error('DataManager: could not load local JSON file either.', jsonError.message);
        }

        state.products = [
            { id: 1, sku: 'INV-001', name: 'Sample Item', category: 'Uncategorized', quantity: 0, unitPrice: 0, reorderLevel: 0, supplier: 'N/A', lastUpdated: new Date().toISOString().slice(0, 10) }
        ];
        return state.products;
    }

    function getProducts() {
        return state.products;
    }

    function getProductById(id) {
        return state.products.find((p) => p.id === Number(id));
    }

    function getProductsByCategory(category) {
        if (!category || category === 'all') return state.products;
        return state.products.filter((p) => p.category === category);
    }

    function getStockStatus(product) {
        if (product.quantity <= 0) return 'out-of-stock';
        if (product.quantity <= product.reorderLevel) return 'low-stock';
        return 'in-stock';
    }

    function getLowStockProducts() {
        return state.products.filter((p) => getStockStatus(p) !== 'in-stock');
    }

    function getStockStatistics(list = state.products) {
        const totalProducts = list.length;
        const totalValue = list.reduce((sum, p) => sum + p.quantity * p.unitPrice, 0);
        const lowStockCount = list.filter((p) => getStockStatus(p) === 'low-stock').length;
        const outOfStockCount = list.filter((p) => getStockStatus(p) === 'out-of-stock').length;
        return { totalProducts, totalValue, lowStockCount, outOfStockCount };
    }

    function getCategorySummary() {
        const summary = {};
        state.products.forEach((p) => {
            if (!summary[p.category]) {
                summary[p.category] = { category: p.category, totalValue: 0, totalQuantity: 0 };
            }
            summary[p.category].totalValue += p.quantity * p.unitPrice;
            summary[p.category].totalQuantity += p.quantity;
        });
        return Object.values(summary);
    }

    function getTopProductsByValue(n = 5, list = state.products) {
        return [...list]
            .sort((a, b) => b.quantity * b.unitPrice - a.quantity * a.unitPrice)
            .slice(0, n);
    }

    function getCategoryHealth(list = state.products) {
        const map = {};
        list.forEach((p) => {
            if (!map[p.category]) {
                map[p.category] = { category: p.category, inStock: 0, lowStock: 0, outOfStock: 0, total: 0 };
            }
            const s = getStockStatus(p);
            if (s === 'in-stock') map[p.category].inStock++;
            else if (s === 'low-stock') map[p.category].lowStock++;
            else map[p.category].outOfStock++;
            map[p.category].total++;
        });
        return Object.values(map).sort((a, b) => a.category.localeCompare(b.category));
    }

    function getReorderItems(list = state.products) {
        return list
            .filter((p) => getStockStatus(p) !== 'in-stock')
            .map((p) => ({
                name: p.name,
                quantity: p.quantity,
                reorderLevel: p.reorderLevel,
                shortfall: Math.max(0, p.reorderLevel - p.quantity)
            }))
            .sort((a, b) => b.shortfall - a.shortfall);
    }

    function recordValueSnapshot() {
        state.valueHistory.push({ time: new Date().toLocaleTimeString(), value: getStockStatistics().totalValue });
        if (state.valueHistory.length > 20) state.valueHistory.shift();
    }

    function getValueHistory() {
        return state.valueHistory;
    }

    function filterByCategory(category) {
        state.activeFilters.category = category;
        return applyFilters();
    }

    function filterByStockStatus(status) {
        state.activeFilters.stockStatus = status;
        return applyFilters();
    }

    function filterByPriceRange(min, max) {
        state.activeFilters.minPrice = min === null || isNaN(min) ? null : min;
        state.activeFilters.maxPrice = max === null || isNaN(max) ? null : max;
        return applyFilters();
    }

    function resetFilters() {
        state.activeFilters = { category: 'all', stockStatus: 'all', minPrice: null, maxPrice: null };
        state.searchQuery = '';
        return applyFilters();
    }

    function applyFilters() {
        let result = [...state.products];
        const { category, stockStatus, minPrice, maxPrice } = state.activeFilters;

        if (category && category !== 'all') {
            result = result.filter((p) => p.category === category);
        }
        if (stockStatus && stockStatus !== 'all') {
            result = result.filter((p) => getStockStatus(p) === stockStatus);
        }
        if (minPrice !== null) {
            result = result.filter((p) => p.unitPrice >= minPrice);
        }
        if (maxPrice !== null) {
            result = result.filter((p) => p.unitPrice <= maxPrice);
        }
        if (state.searchQuery) {
            const q = state.searchQuery.toLowerCase();
            result = result.filter(
                (p) => p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q)
            );
        }
        return result;
    }

    function searchProducts(query) {
        const q = (query || '').toLowerCase().trim();
        if (!q) return state.products;
        return state.products.filter(
            (p) => p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q)
        );
    }

    function updateSearchResults(query) {
        state.searchQuery = (query || '').trim();
        return applyFilters();
    }

    function exportToCSV(data) {
        const headers = ['SKU', 'Name', 'Category', 'Quantity', 'Unit Price', 'Total Value', 'Supplier', 'Stock Status'];
        const rows = data.map((p) => [
            p.sku,
            `"${p.name.replace(/"/g, '""')}"`,
            p.category,
            p.quantity,
            p.unitPrice.toFixed(2),
            (p.quantity * p.unitPrice).toFixed(2),
            p.supplier,
            getStockStatus(p)
        ]);
        return [headers.join(','), ...rows.map((r) => r.join(','))].join('\n');
    }

    function downloadCSV(csvContent, filename) {
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    function simulateInventoryChange() {
        if (state.products.length === 0) return null;
        const index = Math.floor(Math.random() * state.products.length);
        const product = state.products[index];
        const delta = Math.floor(Math.random() * 11) - 5;
        product.quantity = Math.max(0, product.quantity + delta);
        product.lastUpdated = new Date().toISOString().slice(0, 10);
        return { product, delta };
    }

    return {
        CONFIG,
        initializeData,
        getProducts,
        getProductById,
        getProductsByCategory,
        getStockStatus,
        getLowStockProducts,
        getStockStatistics,
        getCategorySummary,
        getTopProductsByValue,
        getCategoryHealth,
        getReorderItems,
        recordValueSnapshot,
        getValueHistory,
        filterByCategory,
        filterByStockStatus,
        filterByPriceRange,
        resetFilters,
        applyFilters,
        searchProducts,
        updateSearchResults,
        exportToCSV,
        downloadCSV,
        simulateInventoryChange
    };
})();