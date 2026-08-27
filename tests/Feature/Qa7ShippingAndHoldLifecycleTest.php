<?php

namespace Tests\Feature;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Models\Transaction;
use App\Services\ShippingStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MarketplaceTesting;
use Tests\TestCase;

/**
 * Ship-by-seller SLA: items unshipped after 48h are auto-cancelled
 * and refunded; delivered earnings unlock after the 14-day hold.
 */
class Qa7ShippingAndHoldLifecycleTest extends TestCase
{
    use MarketplaceTesting, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBase();
    }

    public function test_unshipped_item_is_auto_cancelled_and_refunded_after_48h(): void
    {
        $seller = $this->makeApprovedSeller();
        $product = $this->makeActiveProduct($seller, 100.00, stock: 5);
        $buyer = $this->makeBuyer();

        $order = $this->checkoutPaid($buyer, [$product->id => 1]);

        $this->assertEquals(4, $product->fresh()->stock);
        $item = $order->items()->firstOrFail();
        $this->assertEquals(90.0, (float) $seller->fresh()->pending_balance);

        $this->travel(49)->hours();

        $this->artisan('soukelkom:cancel-unshipped-items')->assertSuccessful();

        $item->refresh();
        $this->assertEquals(OrderItemStatus::Cancelled, $item->status);
        $this->assertStringContainsString('48 hours', (string) $item->cancellation_reason);

        $this->assertEquals(5, $product->fresh()->stock, 'Stock must be restored.');
        $this->assertEquals(0.0, (float) $seller->fresh()->pending_balance, 'Held earning reversed.');

        $refund = Transaction::query()->where('type', 'refund')->where('order_item_id', $item->id)->firstOrFail();
        $this->assertEquals('-90.00', $refund->amount);

        $order->refresh();
        $this->assertEquals(OrderStatus::Refunded, $order->status);
    }

    public function test_recently_paid_items_are_not_cancelled_early(): void
    {
        $seller = $this->makeApprovedSeller();
        $product = $this->makeActiveProduct($seller, 50);
        $buyer = $this->makeBuyer();

        $order = $this->checkoutPaid($buyer, [$product->id => 1]);

        $this->travel(10)->hours();

        $this->artisan('soukelkom:cancel-unshipped-items')->assertSuccessful();

        $this->assertEquals(OrderItemStatus::AwaitingShipment, $order->items()->first()->status);
    }

    public function test_delivered_earnings_release_after_hold_period(): void
    {
        [$seller] = $this->deliveredScenario();

        $this->travel(15)->days();

        $this->artisan('soukelkom:release-earnings')->assertSuccessful();

        $this->assertEquals(45.0, (float) $seller->fresh()->balance);
        $this->assertEquals(0.0, (float) $seller->fresh()->pending_balance);

        $txn = Transaction::query()->where('type', 'earning')->firstOrFail();
        $this->assertNotNull($txn->released_at);
        $this->assertNotNull($txn->available_at);
    }

    public function test_earnings_stay_held_before_hold_window_ends(): void
    {
        [$seller] = $this->deliveredScenario();

        $this->travel(13)->days();

        $this->artisan('soukelkom:release-earnings')->assertSuccessful();

        $this->assertEquals(0.0, (float) $seller->fresh()->balance, 'Nothing released before 14 days.');
        $this->assertEquals(45.0, (float) $seller->fresh()->pending_balance);
    }

    /** @return array{0:Seller, 1:OrderItem, 2:ShippingStateService} */
    protected function deliveredScenario(): array
    {
        $seller = $this->makeApprovedSeller('Deliverer');
        $product = $this->makeActiveProduct($seller, 50);
        $buyer = $this->makeBuyer();

        $order = $this->checkoutPaid($buyer, [$product->id => 1]);

        $item = $order->items()->firstOrFail();

        $shipping = app(ShippingStateService::class);
        $shipping->markShipped($item, 'Aramex', 'ARX-TEST-001');
        $shipping->markDelivered($item->fresh());

        return [$seller->fresh(), $item->fresh(), $shipping];
    }
}
