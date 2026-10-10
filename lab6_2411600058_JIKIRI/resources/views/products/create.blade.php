@extends('layouts.app')

@section('title', 'Add Product')
@section('page-title', 'Add Product')

@section('content')
<div class="card chart-card">
    <div class="card-body">
        <form method="POST" action="{{ route('products.store') }}">
            @csrf
            @include('products._form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Product</button>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection