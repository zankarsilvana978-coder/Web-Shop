<?php

namespace App\Models;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Order extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public const PROOF_COLLECTION = 'payment_proof';

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'payment_method',
        'subtotal',
        'shipping_fee',
        'total',
        'shipping_name',
        'shipping_phone',
        'shipping_city',
        'shipping_address',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'paid_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
            'subtotal' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->whereIn('status', [OrderStatus::Paid, OrderStatus::Completed]);
    }

    /** True when every item reached a terminal state. */
    public function allItemsDelivered(): bool
    {
        return $this->items->every(fn (OrderItem $item) => $item->status === OrderItemStatus::Delivered);
    }

    public static function generateOrderNumber(): string
    {
        do {
            $number = 'SO-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        } while (self::where('order_number', $number)->exists());

        return $number;
    }
}
