<?php

namespace App\Models;

use App\Enums\SellerStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seller extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'store_name',
        'slug',
        'description',
        'phone',
        'status',
        'rejection_reason',
        'commission_rate',
        'balance',
        'pending_balance',
    ];

    protected function casts(): array
    {
        return [
            'status' => SellerStatus::class,
            'commission_rate' => 'decimal:2',
            'balance' => 'decimal:2',
            'pending_balance' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->latest();
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class)->latest();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', SellerStatus::Approved);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', SellerStatus::Pending);
    }
}
