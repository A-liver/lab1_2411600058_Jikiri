@extends('layouts.app')

@section('title', 'Transaction #' . $transaction->id)
@section('page-title', 'Transaction #' . $transaction->id)

@section('content')
@php $signed = $transaction->signed_quantity; @endphp

<div class="row g-3">
    <div class="col-md-8">
        <div class="card chart-card mb-3">
            <div class="card-header fw-bold">Transaction Details</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Date / Time</dt>
                    <dd class="col-sm-8">{{ $transaction->created_at->format('Y-m-d H:i:s') }}</dd>
                    <dt class="col-sm-4">Type</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-{{ $transaction->type_color }} {{ $transaction->type_color === 'warning' ? 'text-dark' : '' }}">
                            {{ $transaction->type_label }}
                        </span>
                    </dd>
                    <dt class="col-sm-4">Quantity</dt>
                    <dd class="col-sm-8 fw-bold {{ $signed < 0 ? 'text-danger' : 'text-success' }}">
                        {{ $signed > 0 ? '+' : '' }}{{ $signed }}
                    </dd>
                    <dt class="col-sm-4">Balance After</dt>
                    <dd class="col-sm-8">{{ $transaction->balance_after ?? '—' }}</dd>
                    <dt class="col-sm-4">Reference</dt>
                    <dd class="col-sm-8">{{ $transaction->reference_number ?: '—' }}</dd>
                    <dt class="col-sm-4">Reason</dt>
                    <dd class="col-sm-8">
                        {{ \App\Models\InventoryTransaction::ADJUSTMENT_REASONS[$transaction->reason] ?? ($transaction->reason ?: '—') }}
                    </dd>
                    <dt class="col-sm-4">Notes</dt>
                    <dd class="col-sm-8">{{ $transaction->notes ?: '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card chart-card">
            <div class="card-header fw-bold">Product</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8"><a href="{{ route('products.show', $transaction->product) }}">{{ $transaction->product->name }}</a></dd>
                    <dt class="col-sm-4">SKU</dt>
                    <dd class="col-sm-8">{{ $transaction->product->sku }}</dd>
                    <dt class="col-sm-4">Current Stock</dt>
                    <dd class="col-sm-8">
                        {{ $transaction->product->quantity }}
                        <span class="badge bg-{{ $transaction->product->stock_status_color }} {{ $transaction->product->stock_status_color === 'warning' ? 'text-dark' : '' }}">
                            {{ $transaction->product->stock_status_label }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card chart-card mb-3">
            <div class="card-header fw-bold">Recorded By</div>
            <div class="card-body">
                @if ($transaction->user)
                    <div class="fw-bold">{{ $transaction->user->name }}</div>
                    <div class="text-muted">{{ $transaction->user->email }}</div>
                    <span class="badge bg-secondary mt-1">{{ ucfirst($transaction->user->role) }}</span>
                @else
                    <span class="text-muted">System / deleted user</span>
                @endif
            </div>
        </div>

        <div class="card chart-card">
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Back to List</a>
                @can('admin')
                    <form method="POST" action="{{ route('transactions.destroy', $transaction) }}"
                          onsubmit="return confirm('Delete this transaction and reverse its stock change?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">Delete</button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection