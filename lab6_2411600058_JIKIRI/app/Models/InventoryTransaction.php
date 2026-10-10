<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Observers\InventoryTransactionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Support\Facades\DB;

#[ObservedBy([InventoryTransactionObserver::class])]
class InventoryTransaction extends Model
{
    public const TYPE_IN = 'stock_in';
    public const TYPE_OUT = 'stock_out';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public const ADJUSTMENT_REASONS = [
        'physical_count' => 'Physical count discrepancy',
        'damaged'        => 'Damaged goods',
        'theft_loss'     => 'Theft / loss',
        'system_error'   => 'System error',
    ];

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'balance_after',
        'reference_number',
        'reason',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'balance_after' => 'integer',
    ];

    /** Valid transaction types, for validation rules and form dropdowns. */
    public static function types(): array
    {
        return [
            self::TYPE_IN => 'Stock In',
            self::TYPE_OUT => 'Stock Out',
            self::TYPE_ADJUSTMENT => 'Adjustment',
        ];
    }

    // ---------- Relationships ----------

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** The user who performed (is accountable for) the movement. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ---------- Query scopes ----------

    public function scopeStockIn(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_IN);
    }

    public function scopeStockOut(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_OUT);
    }

    public function scopeAdjustments(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_ADJUSTMENT);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    // ---------- Accessors ----------

    /** Effect on stock: negative for stock-out, as-stored for stock-in/adjustment. */
    public function getSignedQuantityAttribute(): int
    {
        return $this->type === self::TYPE_OUT ? -$this->quantity : $this->quantity;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::types()[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }
    public static function record(array $data): self
    {
    return DB::transaction(fn () => static::create($data));
    }

    /** Bootstrap badge colour for the type. */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_IN => 'success',
            self::TYPE_OUT => 'danger',
            default => 'warning',
        };
    }
}