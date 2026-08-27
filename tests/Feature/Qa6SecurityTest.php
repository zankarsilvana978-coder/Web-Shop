<?php

namespace Tests\Feature;

use App\Livewire\Seller\OrdersPage;
use App\Livewire\Seller\ProductsPage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\MarketplaceTesting;
use Tests\TestCase;

/**
 * QA TEST 6 — SECURITY
 * Seller A tries to edit Seller B product => 403 Forbidden.
 */
class Qa6SecurityTest extends TestCase
{
    use MarketplaceTesting, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBase();
    }

    public function test_seller_a_cannot_edit_seller_b_product_via_component_url(): void
    {
        $sellerA = $this->makeApprovedSeller('Store A');
        $sellerB = $this->makeApprovedSeller('Store B');

        $foreignProduct = $this->makeActiveProduct($sellerB, 99.00);

        $this->actingAs($sellerA->user);

        Livewire::test(ProductsPage::class)
            ->call('edit', $foreignProduct->id)
            ->assertForbidden();
    }

    public function test_seller_a_cannot_delete_seller_b_product(): void
    {
        $sellerA = $this->makeApprovedSeller('Store A');
        $sellerB = $this->makeApprovedSeller('Store B');

        $foreignProduct = $this->makeActiveProduct($sellerB, 10);

        $this->actingAs($sellerA->user)
            ->get('/seller/products')
            ->assertOk();

        $this->assertFalse($sellerA->user->can('update', $foreignProduct));
        $this->assertFalse($sellerA->user->can('delete', $foreignProduct));

        Livewire::test(ProductsPage::class)
            ->call('delete', $foreignProduct->id)
            ->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $foreignProduct->id, 'deleted_at' => null]);
    }

    public function test_seller_cannot_ship_another_sellers_item(): void
    {
        [$order, $foreignItem] = $this->paidOrderForSeller();

        $intruder = $this->makeApprovedSeller('Intruder Inc');
        $this->actingAs($intruder->user);

        Livewire::test(OrdersPage::class)
            ->set('shippingItemId', $foreignItem->id)
            ->call('markShipped')
            ->assertForbidden();
    }

    public function test_buyer_is_forbidden_from_admin_and_seller_areas(): void
    {
        $buyer = $this->makeBuyer();

        $this->actingAs($buyer)
            ->get('/admin')
            ->assertForbidden();

        $this->actingAs($buyer)
            ->get('/seller')
            ->assertForbidden();
    }

    /** @return array{0: Order, 1: OrderItem} */
    protected function paidOrderForSeller(): array
    {
        $seller = $this->makeApprovedSeller('Victim Store');
        $product = $this->makeActiveProduct($seller, 40);
        $buyer = $this->makeBuyer();

        $order = $this->checkoutPaid($buyer, [$product->id => 1]);

        return [$order, $order->items()->firstOrFail()];
    }
}
