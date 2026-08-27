<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Enums\SellerStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Searchable, SoftDeletes;

    public const IMAGE_COLLECTION = 'images';

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'sku',
        'status',
        'rejection_reason',
        'commission_rate',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'price' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'is_featured' => 'boolean',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::Active)
            ->whereHas('seller', fn (Builder $q) => $q->where('status', SellerStatus::Approved))
            ->where('stock', '>', 0);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::IMAGE_COLLECTION);
    }

    /**
     * Scout index. Collection engine performs as-you-type LIKE search
     * over these fields locally; Meilisearch uses the same contract in production.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => (string) $this->description,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === ProductStatus::Active;
    }
}
