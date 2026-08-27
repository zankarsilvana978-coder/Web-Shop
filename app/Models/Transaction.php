<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /**
     * Immutable ledger row. Never update amount/type after creation;
     * corrections are new rows (e.g. refunds).
     */
    protected $fillable = [
        'seller_id',
        'order_item_id',
        'payout_id',
        'type',
        'amount',
        'description',
        'available_at',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => 'decimal:2',
            'available_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(Payout::class);
    }

    public function scopeReleasable(Builder $query): Builder
    {
        return $query->where('type', TransactionType::Earning)
            ->whereNull('released_at')
            ->whereNotNull('available_at')
            ->where('available_at', '<=', now());
    }
}
