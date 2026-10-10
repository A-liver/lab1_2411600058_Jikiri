@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title')
    <span id="greeting">
        Hello, {{ auth()->user()->name ?? 'User' }}!
    </span>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <small class="text-muted">
        <span id="pollStatus" class="badge bg-success">Live</span>
        Last updated: <span id="lastUpdated">{{ now()->format('M j, Y g:i:s A') }}</span>
        &middot; refreshes every 30 seconds
    </small>
    <button type="button" id="refreshNow" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-arrow-clockwise"></i> Refresh now
    </button>
</div>

<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card shadow-hover text-center">
            <div class="card-body py-3">
                <h6 class="card-title text-muted mb-1">Total Products</h6>
                <h4 class="fw-bold text-primary mb-0" id="invTotalProducts">{{ $totalProducts }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card shadow-hover text-center">
            <div class="card-body py-3">
                <h6 class="card-title text-muted mb-1">Inventory Value</h6>
                <h4 class="fw-bold text-success mb-0" id="invTotalValue">${{ number_format($totalValue, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card shadow-hover text-center">
            <div class="card-body py-3">
                <h6 class="card-title text-muted mb-1">Low Stock</h6>
                <h4 class="fw-bold text-warning mb-0" id="invLowStock">{{ $lowStockCount }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card stat-card shadow-hover text-center">
            <div class="card-body py-3">
                <h6 class="card-title text-muted mb-1">Out of Stock</h6>
                <h4 class="fw-bold text-danger mb-0" id="invOutOfStock">{{ $outOfStockCount }}</h4>
            </div>
        </div>
    </div>
</div>

<div id="lowStockAlert">
    @if ($lowStockProducts->isNotEmpty() || $outOfStockProducts->isNotEmpty())
        <div class="alert alert-warning alert-dismissible fade show d-flex" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 flex-shrink-0"></i>
            <div>
                <strong>{{ $lowStockProducts->count() + $outOfStockProducts->count() }} item(s) need attention</strong>
                <ul class="mb-0 mt-2 small">
                    {{-- OUT OF STOCK - MOST URGENT --}}
                    @foreach ($outOfStockProducts as $p)
                        <li>
                            <strong class="text-danger">{{ $p->name }}</strong> —
                            <strong>OUT OF STOCK</strong> (reorder at {{ $p->reorder_level }})
                        </li>
                    @endforeach

                    {{-- LOW STOCK --}}
                    @foreach ($lowStockProducts as $p)
                        <li>
                            <strong>{{ $p->name }}</strong> —
                            {{ $p->quantity }} left (reorder at {{ $p->reorder_level }})
                        </li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>

<div class="row mb-4">
    <div class="col-lg-4 mb-3">
        <div class="card chart-card h-100">
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categoryValueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card chart-card h-100">
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="stockStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card chart-card h-100">
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recently Updated Products</h5>
        </div>
        <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Transactions</h5>
            <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead>
                        <tr class="border-accent">
                            <th>Date / Time</th><th>Product</th><th>Type</th>
                            <th class="text-end">Qty</th><th>User</th>
                        </tr>
                    </thead>
                    <tbody id="recentTransactionsBody">
                        @forelse ($recentTransactions as $t)
                            <tr>
                                <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                                <td><a href="{{ route('products.show', $t->product_id) }}">{{ $t->product->name }}</a></td>
                                <td>
                                    <span class="badge bg-{{ $t->type_color }} {{ $t->type_color === 'warning' ? 'text-dark' : '' }}">{{ $t->type_label }}</span>
                                </td>
                                <td class="text-end fw-bold {{ $t->signed_quantity < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $t->signed_quantity > 0 ? '+' : '' }}{{ $t->signed_quantity }}
                                </td>
                                <td>{{ $t->user->name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No transactions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr class="border-accent"><th>Name</th><th>Category</th><th>Qty</th><th>Updated</th></tr>
                </thead>
                <tbody id="recentProductsBody">
                    @foreach ($recentProducts as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->category }}</td>
                            <td>{{ $p->quantity }}</td>
                            <td>{{ $p->updated_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    window.DASHBOARD_API = {
        stats: @json(url('/api/dashboard/stats')),
        lowStock: @json(url('/api/products/low-stock')),
        recent: @json(url('/api/transactions/recent?limit=8')),
    };
</script>
<script src="{{ asset('JavaScript/dashboard-poll.js') }}"></script>

<script>
    // Minimal shim: gives Lab 4's charts.js a DataManager to talk to,
    // backed by real Laravel/MySQL data instead of products.json.
    window.DataManager = (function () {
        const products = @json($productsForCharts);

        function getStockStatus(product) {
            if (product.quantity <= 0) return 'out-of-stock';
            if (product.quantity <= product.reorderLevel) return 'low-stock';
            return 'in-stock';
        }

        function getProducts() {
            return products;
        }

        function getStockStatistics() {
            const totalProducts = products.length;
            const lowStockCount = products.filter(p => getStockStatus(p) === 'low-stock').length;
            const outOfStockCount = products.filter(p => getStockStatus(p) === 'out-of-stock').length;
            return { totalProducts, lowStockCount, outOfStockCount };
        }

        function getTopProductsByValue(n = 5) {
            return [...products]
                .sort((a, b) => b.quantity * b.unitPrice - a.quantity * a.unitPrice)
                .slice(0, n);
        }

        return { getProducts, getStockStatus, getStockStatistics, getTopProductsByValue };
    })();
</script>

<script src="{{ asset('JavaScript/charts.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', renderCharts);
</script>
@endpush

@endsection