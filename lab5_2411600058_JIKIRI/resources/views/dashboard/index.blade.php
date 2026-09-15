@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-accent">
            <div class="card-body">
                <div class="card-title text-muted">📦 Total Products</div>
                <div class="card-text fw-bold text-primary">{{ $totalProducts }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-accent">
            <div class="card-body">
                <div class="card-title text-muted">💰 Inventory Value</div>
                <div class="card-text fw-bold text-success">${{ number_format($totalValue, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-accent">
            <div class="card-body">
                <div class="card-title text-muted">⚠️ Low Stock</div>
                <div class="card-text fw-bold text-warning">{{ $lowStockCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-accent">
            <div class="card-body">
                <div class="card-title text-muted">🚫 Out of Stock</div>
                <div class="card-text fw-bold text-danger">{{ $outOfStockCount }}</div>
            </div>
        </div>
    </div>
</div>

@if ($lowStockProducts->isNotEmpty())
    <div class="alert alert-warning d-flex">
        <i class="bi bi-exclamation-triangle-fill fs-4 me-3 flex-shrink-0"></i>
        <div>
            <strong>{{ $lowStockProducts->count() }} item(s) need attention</strong>
            <ul class="mb-0 mt-2 small">
                @foreach ($lowStockProducts as $p)
                    <li><strong>{{ $p->name }}</strong> — {{ $p->quantity <= 0 ? 'OUT OF STOCK' : "{$p->quantity} left (reorder at {$p->reorder_level})" }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@endsection