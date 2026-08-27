<?php

namespace Database\Factories;

use App\Enums\SellerStatus;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Seller>
 */
class SellerFactory extends Factory
{
    protected $model = Seller::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'user_id' => User::factory(),
            'store_name' => $name,
            'slug' => Str::slug($name).'-'.strtolower(Str::random(4)),
            'description' => fake()->sentence(10),
            'phone' => fake()->phoneNumber(),
            'status' => SellerStatus::Approved,
            'balance' => 0,
            'pending_balance' => 0,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => SellerStatus::Pending]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => SellerStatus::Suspended]);
    }

    public function withCommission(?float $rate): static
    {
        return $this->state(fn () => ['commission_rate' => $rate]);
    }
}
