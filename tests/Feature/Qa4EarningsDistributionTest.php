<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Jobs\DistributeEarnings;
use App\Models\Seller;
use App\Models\Transaction;
use App\Notifications\AdminOrderPaidNotification;
use App\Notifications\SellerNewPaidOrderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Support\MarketplaceTesting;
use Tests\TestCase;

/**
 * QA TEST 4 — EARNINGS DISTRIBUTION
 * Mark Order as Paid => Seller A balance += correct amount,
 * Seller B balance += correct amount, transaction log created.
 */
class Qa4EarningsDistributionTest extends TestCase
{
    use MarketplaceTesting, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBase();
    }

    public function test_paying_order_credits_sellers_and_writes_ledger(): void
    {
        Notification::fake();

        $sellerA = $this->makeApprovedSeller('Seller A');
        $sellerB = $this->makeApprovedSeller('Seller B');
        $admin = $this->makeAdmin();

        $productA = $this->makeActiveProduct($sellerA, 50.00);
        $productB = $this->makeActiveProduct($sellerB, 20.00);

        $buyer = $this->makeBuyer();

        $order = $this->checkoutPaid($buyer, [$productA->id => 1, $productB->id => 1]);

        $this->assertEquals(OrderStatus::Paid, $order->status);

        // Balances credited into the pending (on-hold) bucket.
        $this->assertEquals(45.0, (float) $sellerA->fresh()->pending_balance);
        $this->assertEquals(18.0, (float) $sellerB->fresh()->pending_balance);
        $this->assertEquals(0.0, (float) $sellerA->fresh()->balance);

        // Ledger: earning + commission per item = 4 rows.
        $ledger = Transaction::query()->whereHas('seller', fn ($q) => $q->whereIn('id', [$sellerA->id, $sellerB->id]))->get();
        $this->assertCount(4, $ledger);

        $earningA = Transaction::query()->where('seller_id', $sellerA->id)->where('type', 'earning')->first();
        $commissionA = Transaction::query()->where('seller_id', $sellerA->id)->where('type', 'commission')->first();

        $this->assertEquals('45.00', $earningA->amount);
        $this->assertEquals('5.00', $commissionA->amount);

        // Notifications reached admins and both sellers.
        Notification::assertSentTo([$admin], AdminOrderPaidNotification::class);
        Notification::assertSentTo([$sellerA->user], SellerNewPaidOrderNotification::class);
        Notification::assertSentTo([$sellerB->user], SellerNewPaidOrderNotification::class);
    }

    public function test_distribution_is_idempotent_when_job_retries(): void
    {
        $seller = $this->makeApprovedSeller();
        $product = $this->makeActiveProduct($seller, 100.00);
        $buyer = $this->makeBuyer();

        $order = $this->checkoutPaid($buyer, [$product->id => 1]);

        DistributeEarnings::dispatchSync($order);
        DistributeEarnings::dispatchSync($order);

        $this->assertEquals(2, Transaction::query()->count());
        $this->assertEquals(90.0, (float) $seller->fresh()->pending_balance, 'Balance must not double-credit.');
    }
}
