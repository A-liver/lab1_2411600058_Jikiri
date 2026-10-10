<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;
use App\Models\InventoryTransaction;
use App\Services\InventoryStats;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = InventoryStats::summary();

        $totalProducts = $stats['total_products'];
        $lowStockCount = $stats['low_stock'];
        $outOfStockCount = $stats['out_of_stock'];
        $inStockCount = $stats['in_stock'];
        $totalValue = $stats['inventory_value'];

        $recentTransactions = InventoryTransaction::with(['product', 'user'])->recent()->limit(8)->get();

        // LOW STOCK (exclude out of stock)
        $lowStockProducts = Product::whereColumn('quantity', '<=', 'reorder_level')
            ->where('quantity', '>', 0)
            ->orderBy('quantity')
            ->limit(6)
            ->get();

        // OUT OF STOCK
        $outOfStockProducts = Product::where('quantity', '<=', 0)
            ->orderBy('name')
            ->get();

        $recentProducts = Product::latest('updated_at')->limit(6)->get();

        $categoryBreakdown = Product::selectRaw('category, SUM(quantity * unit_price) as total_value')
            ->groupBy('category')
            ->orderByDesc('total_value')
            ->get();

        $topProducts = Product::all()
            ->sortByDesc(fn ($p) => $p->quantity * $p->unit_price)
            ->take(10)
            ->values();
            
        $productsForCharts = Product::all()->map(fn ($p) => [
            'name' => $p->name,
            'category' => $p->category,
            'quantity' => $p->quantity,
            'unitPrice' => (float) $p->unit_price,
            'reorderLevel' => $p->reorder_level,
            ]);

        return view('dashboard.index', compact(
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'inStockCount', 
            'totalValue',
            'lowStockProducts',
            'outOfStockProducts',
            'recentProducts',
            'categoryBreakdown',
            'topProducts',
            'productsForCharts',
            'recentTransactions'

        ));
    }
}