@extends('layouts.app')

@section('title', 'Inventory')
@section('page-title', 'Inventory Management')

@section('content')

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('products.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="categoryFilter" class="form-label">Category</label>
                    <select id="categoryFilter" name="category" class="form-select" onchange="this.form.submit()">
                        <option value="all">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label d-block">Stock Status</label>
                    <div class="btn-group w-100" role="group" aria-label="Stock status filter">
                        @foreach (['all' => 'All', 'in-stock' => 'In', 'low-stock' => 'Low', 'out-of-stock' => 'Out'] as $value => $label)
                            @php
                                $colors = ['all' => 'secondary', 'in-stock' => 'success', 'low-stock' => 'warning', 'out-of-stock' => 'danger'];
                                $active = request('stock_status', 'all') === $value;
                            @endphp
                            <button type="submit" name="stock_status" value="{{ $value }}"
                                    class="btn btn-outline-{{ $colors[$value] }} {{ $active ? 'active' : '' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="minPriceFilter" class="form-label">Min Price</label>
                    <input type="number" min="0" step="0.01" name="min_price" id="minPriceFilter"
                           class="form-control" placeholder="0.00" value="{{ request('min_price') }}">
                </div>

                <div class="col-md-2">
                    <label for="maxPriceFilter" class="form-label">Max Price</label>
                    <input type="number" min="0" step="0.01" name="max_price" id="maxPriceFilter"
                           class="form-control" placeholder="0.00" value="{{ request('max_price') }}">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Apply</button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary flex-fill">Reset</a>
                </div>
            </div>

            <hr>

            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label for="productSearch" class="form-label">Search by name or SKU</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="productSearch" name="search" class="form-control"
                               placeholder="e.g. Chicken, INV-005..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('products.export', request()->query()) }}" class="btn btn-outline-primary" id="exportCsvBtn">
                        <i class="bi bi-download me-1"></i> Export to CSV
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card chart-card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div class="text-muted">{{ $products->total() }} product(s)</div>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Product
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Inventory Items</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr class="border-accent">
                        <th>SKU</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total Value</th>
                        <th>Status</th>
                        <th>Supplier</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="inventoryTableBody">
                    @forelse ($products as $product)
                        @php $status = $product->stock_status; @endphp
                        <tr class="{{ $status === 'low-stock' ? 'row-low-stock' : ($status === 'out-of-stock' ? 'row-out-of-stock' : '') }}">
                            <td>{!! highlight_match($product->sku, request('search')) !!}</td>
                            <td>{!! highlight_match($product->name, request('search')) !!}</td>
                            <td>{{ $product->category }}</td>
                            <td>{{ $product->quantity }}</td>
                            <td>${{ number_format($product->unit_price, 2) }}</td>
                            <td>${{ number_format($product->quantity * $product->unit_price, 2) }}</td>
                            <td>
                                @if ($status === 'in-stock')
                                    <span class="badge bg-success">In Stock</span>
                                @elseif ($status === 'low-stock')
                                    <span class="badge bg-warning text-dark">Low Stock</span>
                                @else
                                    <span class="badge bg-danger">Out of Stock</span>
                                @endif
                            </td>
                            <td>{{ $product->supplier }}</td>
                            <td class="text-end">
                                <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" class="d-inline"
                                      onsubmit="return confirm('Delete {{ $product->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No products match the current filters/search.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">{{ $products->links() }}</div>


@endsection