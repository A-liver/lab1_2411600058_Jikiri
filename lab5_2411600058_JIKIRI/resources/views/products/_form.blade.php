@php $p = $product ?? null; @endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Product Name</label>
        <input type="text" name="name" value="{{ old('name', $p->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">SKU</label>
        <input type="text" name="sku" value="{{ old('sku', $p->sku ?? '') }}" class="form-control @error('sku') is-invalid @enderror" required>
        @error('sku') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $p->description ?? '') }}</textarea>
    </div>

    <div class="col-md-4">
        <label class="form-label">Category</label>
        <input type="text" name="category" value="{{ old('category', $p->category ?? '') }}" class="form-control @error('category') is-invalid @enderror" required>
        @error('category') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Supplier</label>
        <input type="text" name="supplier" value="{{ old('supplier', $p->supplier ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Unit Price ($)</label>
        <input type="number" step="0.01" min="0" name="unit_price" value="{{ old('unit_price', $p->unit_price ?? '') }}" class="form-control @error('unit_price') is-invalid @enderror" required>
        @error('unit_price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Quantity in Stock</label>
        <input type="number" min="0" name="quantity" value="{{ old('quantity', $p->quantity ?? 0) }}" class="form-control @error('quantity') is-invalid @enderror" required>
        @error('quantity') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Reorder Level</label>
        <input type="number" min="0" name="reorder_level" value="{{ old('reorder_level', $p->reorder_level ?? 0) }}" class="form-control @error('reorder_level') is-invalid @enderror" required>
        @error('reorder_level') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>
</div>