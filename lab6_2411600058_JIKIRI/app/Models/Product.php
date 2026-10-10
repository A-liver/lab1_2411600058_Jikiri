<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'description',
        'category',
        'quantity',
        'reorder_level',
        'unit_price',
        'supplier',
    ];

    // Not mass-assignable: only the alert system touches this.
    protected $casts = [
        'quantity' => 'integer',
        'reorder_level' => 'integer',
        'unit_price' => 'decimal:2',
        'last_low_stock_notified_at' => 'datetime',
    ];

    // ---------- Relationships ----------

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function latestTransaction(): HasOne
    {
        return $this->hasOne(InventoryTransaction::class)->latestOfMany();
    }

    // ---------- Business logic helpers ----------

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Slug used by the existing Lab 5 views/controllers:
     * 'in-stock' | 'low-stock' | 'out-of-stock'
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'out-of-stock';
        }

        if ($this->isLowStock()) {
            return 'low-stock';
        }

        return 'in-stock';
    }

    /** Human-readable: 'In Stock' | 'Low Stock' | 'Out of Stock' */
    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'out-of-stock' => 'Out of Stock',
            'low-stock' => 'Low Stock',
            default => 'In Stock',
        };
    }

    /** Bootstrap colour class: 'success' | 'warning' | 'danger' */
    public function getStockStatusColorAttribute(): string
    {
        return match ($this->stock_status) {
            'out-of-stock' => 'danger',
            'low-stock' => 'warning',
            default => 'success',
        };
    }

    // ---------- Query scopes ----------

    /** At or below reorder level (includes out-of-stock). */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'reorder_level');
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeInCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }
}