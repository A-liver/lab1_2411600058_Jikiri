<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'type_color' => $this->type_color,
            'quantity' => $this->signed_quantity,   // negative for stock-out
            'balance_after' => $this->balance_after,
            'reference_number' => $this->reference_number,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
            'created_at_display' => $this->created_at->format('Y-m-d H:i'),
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'sku' => $this->product->sku,
                'url' => route('products.show', $this->product),
            ]),
            'user' => $this->whenLoaded('user', fn () => $this->user?->name ?? 'System'),
        ];
    }
}