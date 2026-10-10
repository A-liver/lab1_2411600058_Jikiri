@extends('layouts.app')

@section('title', 'Transactions')
@section('page-title', 'Inventory Transactions')

@section('content')

<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('transactions.create') }}" class="btn btn-primary">+ Record Movement</a>
</div>

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('transactions.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="product_id" class="form-label">Product</label>
                    <select id="product_id" name="product_id" class="form-select">
                        <option value="">All Products</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>
                                {{ $p->name }} ({{ $p->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="type" class="form-label">Type</label>
                    <select id="type" name="type" class="form-select">
                        <option value="">All Types</option>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="from" class="form-label">From</label>
                    <input type="date" id="from" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label for="to" class="form-label">To</label>
                    <input type="date" id="to" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Apply</button>
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary flex-fill">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card chart-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Date / Time</th>
                    <th>Product</th>
                    <th>Type</th>
                    <th class="text-end">Quantity</th>
                    <th class="text-end">Balance</th>
                    <th>Reference</th>
                    <th>User</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $t)
                    @php $signed = $t->signed_quantity; @endphp
                    <tr>
                        <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                        <td><a href="{{ route('products.show', $t->product_id) }}">{{ $t->product->name }}</a></td>
                        <td>
                            <span class="badge bg-{{ $t->type_color }} {{ $t->type_color === 'warning' ? 'text-dark' : '' }}">
                                {{ $t->type_label }}
                            </span>
                        </td>
                        <td class="text-end fw-bold {{ $signed < 0 ? 'text-danger' : 'text-success' }}">
                            {{ $signed > 0 ? '+' : '' }}{{ $signed }}
                        </td>
                        <td class="text-end">{{ $t->balance_after ?? '—' }}</td>
                        <td>{{ $t->reference_number ?: '—' }}</td>
                        <td>{{ $t->user->name ?? 'System' }}</td>
                        <td class="text-end">
                            <a href="{{ route('transactions.show', $t) }}" class="btn btn-sm btn-outline-primary">View</a>
                            @can('admin')
                                <form method="POST" action="{{ route('transactions.destroy', $t) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this transaction and reverse its stock change?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No transactions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $transactions->links() }}</div>

@endsection