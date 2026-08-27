<?php

namespace Tests\Support;

use App\Enums\ProductStatus;
use App\Enums\SellerStatus;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;

/**
 * Shared fixtures for marketplace QA scenarios.
 */
trait MarketplaceTesting
{
    protected function seedBase(): void
    {
        $this->seed(RoleSeeder::class);
    }

    protected function makeAdmin(): User
    {
        $admin = User::factory()->create(['name' => 'God Mode']);
        $admin->assignRole('admin');

        return $admin;
    }

    protected function makeBuyer(): User
    {
        $buyer = User::factory()->create();
        $buyer->assignRole('buyer');

        return $buyer;
    }

    protected function makeApprovedSeller(string $storeName = 'Ahmed Electronics'): Seller
    {
        $user = User::factory()->create();
        $user->assignRole('seller');

        $seller = Seller::query()->create([
            'user_id' => $user->id,
            'store_name' => $storeName,
            'slug' => Str::slug($storeName).'-'.Str::lower(Str::random(5)),
            'status' => SellerStatus::Approved,
        ]);

        return $seller->fresh();
    }

    protected function makeActiveProduct(Seller $seller, float $price, int $stock = 10): Product
    {
        $name = 'Product '.Str::random(8);

        return Product::query()->create([
            'seller_id' => $seller->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'description' => 'A wonderful demo product description long enough.',
            'price' => $price,
            'stock' => $stock,
            'sku' => strtoupper(Str::random(8)),
            'status' => ProductStatus::Active,
        ]);
    }

    /**
     * Full checkout flow through the real services:
     * cart -> order -> payment marked paid.
     */
    protected function checkoutPaid(User $buyer, array $quantitiesByProductId, string $method = 'manual_transfer')
    {
        $cart = app(CartService::class);
        $checkout = app(CheckoutService::class);

        foreach ($quantitiesByProductId as $productId => $qty) {
            $cart->add($buyer, Product::findOrFail($productId), $qty);
        }

        $order = $checkout->placeOrder($buyer, [
            'payment_method' => $method,
            'shipping_name' => 'John Doe',
            'shipping_phone' => '+961 3 000 000',
            'shipping_city' => 'Beirut',
            'shipping_address' => 'Hamra Street, Building 42',
        ]);

        app(PaymentService::class)->markOrderPaid($order);

        return $order->refresh();
    }
}
