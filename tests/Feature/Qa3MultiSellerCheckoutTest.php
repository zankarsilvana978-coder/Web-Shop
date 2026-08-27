<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\MarketplaceTesting;
use Tests\TestCase;

/**
 * QA TEST 3 — MULTI-SELLER CHECKOUT
 * Buyer adds product from 2 sellers -> Checkout -> Pay
 * => 1 Order, 2 OrderItems, each with the correct commission.
 *
 * Spec math: $50 (10% -> commission 5.00 / earning 45.00)
 *            $20 (10% -> commission 2.00 / earning 18.00)
 *            subtotal 70 + shipping 5 = total 75 paid ONCE.
 */
class Qa3MultiSellerCheckoutTest extends TestCase
{
    use MarketplaceTesting, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBase();
    }

    public function test_one_order_two_sellers_correct_split_and_single_payment(): void
    {
        $sellerA = $this->makeApprovedSeller('Nike Store A');
        $sellerB = $this->makeApprovedSeller('Tee Shop B');

        $shoes = $this->makeActiveProduct($sellerA, 50.00, 10);
        $shirt = $this->makeActiveProduct($sellerB, 20.00, 10);

        $buyer = $this->makeBuyer();

        $order = $this->checkoutPaid($buyer, [$shoes->id => 1, $shirt->id => 1]);

        $this->assertEquals(1, Order::query()->count(), 'Exactly one order must exist.');
        $this->assertCount(2, $order->items);

        $itemA = $order->items()->where('seller_id', $sellerA->id)->firstOrFail();
        $itemB = $order->items()->where('seller_id', $sellerB->id)->firstOrFail();

        $this->assertEquals('50.00', $itemA->subtotal);
        $this->assertEquals('5.00', $itemA->commission_amount);
        $this->assertEquals('45.00', $itemA->seller_earning);

        $this->assertEquals('20.00', $itemB->subtotal);
        $this->assertEquals('2.00', $itemB->commission_amount);
        $this->assertEquals('18.00', $itemB->seller_earning);

        $this->assertEquals('70.00', $order->subtotal);
        $this->assertEquals('5.00', $order->shipping_fee);
        $this->assertEquals('75.00', $order->total);

        $this->assertEquals(OrderStatus::Paid, $order->status);
        $this->assertNotNull($order->paid_at);
    }

    public function test_stock_cannot_go_negative_on_concurrent_buying(): void
    {
        $seller = $this->makeApprovedSeller();
        $product = $this->makeActiveProduct($seller, 30, stock: 1);
        $buyer1 = $this->makeBuyer();
        $buyer2 = $this->makeBuyer();

        $this->checkoutPaid($buyer1, [$product->id => 1]);
        $this->assertEquals(0, $product->fresh()->stock);

        $this->expectException(ValidationException::class);
        $this->checkoutPaid($buyer2, [$product->id => 1]);
    }
}
