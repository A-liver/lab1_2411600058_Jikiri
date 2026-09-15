<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalProducts = Product::count();

        $lowStockCount = Product::whereColumn('quantity', '<=', 'reorder_level')
            ->where('quantity', '>', 0)
            ->count();

        $outOfStockCount = Product::where('quantity', '<=', 0)->count();

        // In stock = total - low stock - out of stock
        $inStockCount = $totalProducts - $lowStockCount - $outOfStockCount;

        $totalValue = Product::all()->sum(fn ($p) => $p->quantity * $p->unit_price);

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

        return view('dashboard.index', compact(
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'inStockCount', // <-- added
            'totalValue',
            'lowStockProducts',
            'outOfStockProducts',
            'recentProducts',
            'categoryBreakdown',
            'topProducts'
        ));
    }
}