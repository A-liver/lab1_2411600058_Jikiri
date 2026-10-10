<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;

class InventoryStats
{
    public static function summary(): array
    {
        $total = Product::count();

        // "Low" excludes out-of-stock, matching the Lab 5 dashboard.
        $low = Product::whereColumn('quantity', '<=', 'reorder_level')
            ->where('quantity', '>', 0)
            ->count();

        $out = Product::where('quantity', '<=', 0)->count();

        $value = (float) Product::selectRaw('COALESCE(SUM(quantity * unit_price), 0) as value')
            ->value('value');

        return [
            'total_products' => $total,
            'in_stock' => $total - $low - $out,
            'low_stock' => $low,
            'out_of_stock' => $out,
            'inventory_value' => round($value, 2),
            'transactions_today' => InventoryTransaction::whereDate('created_at', today())->count(),
        ];
    }
}