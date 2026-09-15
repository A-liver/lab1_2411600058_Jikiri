<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalProducts = Product::count();

        $lowStockCount = Product::where('quantity', '<=', 'reorder_level')
            ->where('quantity', '>', 0)
            ->count();

        $outOfStockCount = Product::where('quantity', '<=', 0)->count();

        $totalValue = Product::all()->sum(fn ($p) => $p->quantity * $p->unit_price);

        $lowStockProducts = Product::whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity')
            ->limit(6)
            ->get();

        return view('dashboard.index', compact(
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'totalValue',
            'lowStockProducts'
        ));
    }
}