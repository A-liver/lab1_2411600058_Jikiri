<?php

namespace App\Observers;

use App\Events\LowStockDetected;
use App\Exceptions\InsufficientStockException;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryTransactionObserver
{
    /**
     * BEFORE the row is saved: validate and take a snapshot.
     * We use `creating` (not only `created`) so an invalid movement is
     * rejected before anything is written.
     */
    public function creating(InventoryTransaction $tx): void
    {
        $this->assertQuantityIsValid($tx);

        // Accountability: default to whoever is logged in.
        $tx->user_id ??= Auth::id();

        $product = Product::lockForUpdate()->findOrFail($tx->product_id);
        $newQuantity = $product->quantity + $tx->signed_quantity;

        if ($newQuantity < 0) {
            throw new InsufficientStockException($product, abs($tx->signed_quantity));
        }

        $tx->balance_after = $newQuantity; // stock level after this movement
    }

    /**
     * AFTER the row is saved: update the product quantity and check alerts.
     */
    public function created(InventoryTransaction $tx): void
    {
        DB::transaction(function () use ($tx) {
            $product = Product::lockForUpdate()->findOrFail($tx->product_id);

            $newQuantity = $product->quantity + $tx->signed_quantity;

            if ($newQuantity < 0) {
                throw new InsufficientStockException($product, abs($tx->signed_quantity));
            }

            $product->quantity = $newQuantity;

            // Duplicate-alert guard:
            //  - stock recovered above reorder level -> clear the marker
            //  - stock DROPPED to/below reorder level and no alert sent yet -> alert once
            $sendAlert = false;

            if (! $product->isLowStock()) {
                $product->last_low_stock_notified_at = null;
            } elseif ($tx->signed_quantity < 0 && $product->last_low_stock_notified_at === null) {
                $product->last_low_stock_notified_at = now();
                $sendAlert = true;
            }

            $product->save();

            if ($sendAlert) {
                LowStockDetected::dispatch($product); // listener is queued
            }
        });
    }

    private function assertQuantityIsValid(InventoryTransaction $tx): void
    {
        if ($tx->type === InventoryTransaction::TYPE_ADJUSTMENT) {
            if ((int) $tx->quantity === 0) {
                throw new InvalidArgumentException('An adjustment cannot be zero.');
            }
            return;
        }

        if ((int) $tx->quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero for stock in/out.');
        }
    }
}