<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request): View
{
    $query = $this->filteredQuery($request);

    $products = $query->orderBy('name')->paginate(15)->withQueryString();
    $categories = Product::select('category')->distinct()->orderBy('category')->pluck('category');

    return view('products.index', compact('products', 'categories'));
}

public function export(Request $request)
{
    $products = $this->filteredQuery($request)->orderBy('name')->get();

    $filename = 'inventory_report_' . now()->format('Y-m-d') . '.csv';

    return response()->streamDownload(function () use ($products) {
        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['SKU', 'Name', 'Category', 'Quantity', 'Unit Price', 'Total Value', 'Supplier', 'Stock Status']);

        foreach ($products as $p) {
            fputcsv($handle, [
                $p->sku,
                $p->name,
                $p->category,
                $p->quantity,
                number_format($p->unit_price, 2),
                number_format($p->quantity * $p->unit_price, 2),
                $p->supplier,
                $p->stock_status,
            ]);
        }

        fclose($handle);
    }, $filename);
}

private function filteredQuery(Request $request)
{
    $query = Product::query();

    if ($request->filled('category') && $request->input('category') !== 'all') {
        $query->where('category', $request->input('category'));
    }

    if ($request->filled('stock_status') && $request->input('stock_status') !== 'all') {
        match ($request->input('stock_status')) {
            'in-stock' => $query->whereColumn('quantity', '>', 'reorder_level'),
            'low-stock' => $query->whereColumn('quantity', '<=', 'reorder_level')->where('quantity', '>', 0),
            'out-of-stock' => $query->where('quantity', '<=', 0),
            default => null,
        };
    }

    if ($request->filled('min_price')) {
        $query->where('unit_price', '>=', (float) $request->input('min_price'));
    }
    if ($request->filled('max_price')) {
        $query->where('unit_price', '<=', (float) $request->input('max_price'));
    }

    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%");
        });
    }

    return $query;
}
    public function create(): View
    {
        return view('products.create');
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with('success', "Product \"{$validated['name']}\" was added successfully.");
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): View
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product->id);

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', "Product \"{$product->name}\" was updated successfully.");
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', "Product \"{$name}\" was deleted.");
    }

    /**
     * Shared validation rules for store() and update().
     */
    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required', 'string', 'max:50',
                'unique:products,sku' . ($ignoreId ? ",{$ignoreId}" : ''),
            ],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:255'],
        ], [
            'sku.unique' => 'That SKU is already in use by another product.',
            'quantity.min' => 'Quantity cannot be negative.',
            'reorder_level.min' => 'Reorder level cannot be negative.',
            'unit_price.min' => 'Unit price cannot be negative.',
        ]);
    }
}