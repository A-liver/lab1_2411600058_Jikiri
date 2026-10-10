<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\StoreInventoryTransactionRequest;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class InventoryTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'product_id' => ['nullable', 'integer'],
            'type' => ['nullable', Rule::in(array_keys(InventoryTransaction::types()))],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $transactions = InventoryTransaction::with(['product', 'user']) // eager loading
            ->when($filters['product_id'] ?? null, fn ($q, $v) => $q->where('product_id', $v))
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->recent()
            ->paginate(20)
            ->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name', 'sku']);
        $types = InventoryTransaction::types();

        return view('transactions.index', compact('transactions', 'products', 'types'));
    }

    public function create(Request $request): View
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'sku', 'quantity']);
        $types = [
            InventoryTransaction::TYPE_IN => 'Stock In',
            InventoryTransaction::TYPE_OUT => 'Stock Out',
        ];
        $selectedProduct = $request->query('product_id'); // lets product pages link here

        return view('transactions.create', compact('products', 'types', 'selectedProduct'));
    }

    public function store(StoreInventoryTransactionRequest $request): RedirectResponse
    {
        try {
            // record() = one DB transaction; the observer updates the stock.
            $transaction = InventoryTransaction::record([
                ...$request->validated(),
                'user_id' => $request->user()->id,
            ]);
        } catch (InsufficientStockException $e) {
            // Safety net: someone else may have sold the stock a moment ago.
            return back()->withInput()->withErrors(['quantity' => $e->getMessage()]);
        }

        return redirect()
            ->route('transactions.index')
            ->with('success', "{$transaction->type_label} of {$transaction->quantity} recorded for "
                . "\"{$transaction->product->name}\". New stock: {$transaction->balance_after}.");
    }

    public function show(InventoryTransaction $transaction): View
    {
        $transaction->load(['product', 'user']);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Admin only. Ledger rows are immutable, so "delete" means UNDO:
     * only the most recent transaction of a product, and its stock effect is reversed.
     * (Older entries must be corrected with an adjustment, which keeps balance_after valid.)
     */
    public function destroy(InventoryTransaction $transaction): RedirectResponse
    {
        Gate::authorize('admin');

        try {
            DB::transaction(function () use ($transaction) {
                $product = Product::lockForUpdate()->findOrFail($transaction->product_id);

                $latestId = (int) $product->transactions()->max('id');
                if ($transaction->id !== $latestId) {
                    throw new RuntimeException(
                        'Only the most recent transaction of a product can be deleted. '
                        . 'Record an adjustment to correct older entries.'
                    );
                }

                $newQuantity = $product->quantity - $transaction->signed_quantity;
                if ($newQuantity < 0) {
                    throw new RuntimeException('Deleting this transaction would make stock negative.');
                }

                $product->quantity = $newQuantity;
                if (! $product->isLowStock()) {
                    $product->last_low_stock_notified_at = null;
                }
                $product->save();

                $transaction->delete();
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['delete' => $e->getMessage()]);
        }

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaction deleted and the stock change was reversed.');
    }
}