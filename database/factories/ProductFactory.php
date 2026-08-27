<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'seller_id' => Seller::factory(),
            'category_id' => Category::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name).'-'.strtolower(Str::random(4)),
            'description' => fake()->paragraph(3),
            'price' => fake()->randomFloat(2, 5, 500),
            'stock' => fake()->numberBetween(1, 100),
            'sku' => strtoupper(Str::random(8)),
            'status' => ProductStatus::Active,
            'commission_rate' => null,
            'is_featured' => false,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => ProductStatus::Draft]);
    }

    public function pendingReview(): static
    {
        return $this->state(fn () => ['status' => ProductStatus::PendingReview]);
    }

    public function rejected(string $reason = 'Does not meet guidelines'): static
    {
        return $this->state(fn () => ['status' => ProductStatus::Rejected, 'rejection_reason' => $reason]);
    }
}
