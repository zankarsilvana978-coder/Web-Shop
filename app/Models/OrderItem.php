<?php

namespace App\Models;

use App\Enums\OrderItemStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'seller_id',
        'product_id',
        'product_name',
        'sku',
        'unit_price',
        'quantity',
        'subtotal',
        'commission_rate',
        'commission_amount',
        'seller_earning',
        'status',
        'tracking_number',
        'carrier',
        'cancellation_reason',
        'shipped_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderItemStatus::class,
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'seller_earning' => 'decimal:2',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /** Earnings that are still inside the post-delivery hold window. */
    public function scopeShippable(Builder $query): Builder
    {
        return $query->where('status', OrderItemStatus::AwaitingShipment);
    }
}
