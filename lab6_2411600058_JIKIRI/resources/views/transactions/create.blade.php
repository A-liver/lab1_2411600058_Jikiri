@extends('layouts.app')

@section('title', 'Record Movement')
@section('page-title', 'Record Stock Movement')

@section('content')
<div class="row">
    <div class="col-lg-7">
        <div class="card chart-card">
            <div class="card-body">
                <form method="POST" action="{{ route('transactions.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product</label>
                        <select id="product_id" name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                            <option value="">-- Select a product --</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}" data-stock="{{ $p->quantity }}"
                                    @selected(old('product_id', $selectedProduct) == $p->id)>
                                    {{ $p->name }} ({{ $p->sku }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div id="stockInfo" class="form-text">Select a product to see its current stock.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Transaction Type</label>
                        @foreach ($types as $value => $label)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="type_{{ $value }}"
                                       value="{{ $value }}" @checked(old('type', 'stock_in') === $value)>
                                <label class="form-check-label" for="type_{{ $value }}">{{ $label }}</label>
                            </div>
                        @endforeach
                        @error('type') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" id="quantity" name="quantity" min="1" step="1"
                               class="form-control @error('quantity') is-invalid @enderror"
                               value="{{ old('quantity') }}" required>
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div id="qtyHint" class="form-text"></div>
                    </div>

                    <div class="mb-3">
                        <label for="reference_number" class="form-label">Reference Number</label>
                        <input type="text" id="reference_number" name="reference_number" maxlength="100"
                               class="form-control" placeholder="e.g. PO-1001 / SO-2045"
                               value="{{ old('reference_number') }}">
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" rows="3" maxlength="1000" class="form-control">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Transaction</button>
                        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const productEl = document.getElementById('product_id');
    const qtyEl     = document.getElementById('quantity');
    const stockInfo = document.getElementById('stockInfo');
    const qtyHint   = document.getElementById('qtyHint');

    function refreshStockUI() {
        const option = productEl.selectedOptions[0];
        const stock  = option && option.value ? parseInt(option.dataset.stock, 10) : null;
        const type   = document.querySelector('input[name="type"]:checked')?.value;

        stockInfo.textContent = stock === null
            ? 'Select a product to see its current stock.'
            : `Current stock: ${stock}`;

        qtyEl.min = 1;
        if (type === 'stock_out' && stock !== null) {
            qtyEl.max = stock;                       // dynamic max for stock-out
            qtyHint.textContent = `Maximum you can remove: ${stock}`;
        } else {
            qtyEl.removeAttribute('max');
            qtyHint.textContent = '';
        }
    }

    productEl.addEventListener('change', refreshStockUI);
    document.querySelectorAll('input[name="type"]').forEach(r => r.addEventListener('change', refreshStockUI));
    refreshStockUI();
</script>
@endpush