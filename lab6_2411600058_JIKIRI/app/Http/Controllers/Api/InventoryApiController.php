<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryTransactionResource;
use App\Http\Resources\ProductResource;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Services\InventoryStats;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryApiController extends Controller
{
    /** GET /api/dashboard/stats */
    public function stats(): JsonResponse
    {
        return response()->json([
            'data' => InventoryStats::summary(),
            'meta' => $this->meta(),
        ]);
    }

    /** GET /api/products/low-stock  (includes out-of-stock, most urgent first) */
    public function lowStock(): AnonymousResourceCollection
    {
        $products = Product::lowStock()->orderBy('quantity')->orderBy('name')->get();

        return ProductResource::collection($products)
            ->additional(['meta' => $this->meta() + ['count' => $products->count()]]);
    }

    /** GET /api/products/{product}  (used by the adjustment form in step 6) */
    public function product(Product $product): JsonResource
    {
        return (new ProductResource($product))->additional(['meta' => $this->meta()]);
    }

    /** GET /api/transactions/recent?limit=10 */
    public function recent(Request $request): AnonymousResourceCollection
    {
        $limit = min(max((int) $request->query('limit', 10), 1), 50);

        $transactions = InventoryTransaction::with(['product', 'user'])
            ->recent()
            ->limit($limit)
            ->get();

        return InventoryTransactionResource::collection($transactions)
            ->additional(['meta' => $this->meta() + ['count' => $transactions->count()]]);
    }

    private function meta(): array
    {
        return [
            'generated_at' => now()->toIso8601String(),
            'generated_at_display' => now()->format('M j, Y g:i:s A'),
        ];
    }
}