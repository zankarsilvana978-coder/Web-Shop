<?php

namespace Tests\Feature;

use App\Enums\PayoutStatus;
use App\Models\Payout;
use App\Models\Seller;
use App\Models\Transaction;
use App\Services\PayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\MarketplaceTesting;
use Tests\TestCase;

/**
 * QA TEST 5 — PAYOUT FLOW
 * Seller with $100 balance requests $50 payout -> Admin approves
 * => Payout status = paid. Seller balance = $50.
 */
class Qa5PayoutFlowTest extends TestCase
{
    use MarketplaceTesting, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBase();
    }

    public function test_request_then_admin_approval_results_in_paid_payout(): void
    {
        [$seller] = $this->sellerWithBalance(100);
        $admin = $this->makeAdmin();

        $payout = app(PayoutService::class)->request($seller, 50);

        $this->assertEquals(PayoutStatus::Pending, $payout->status);
        $this->assertEquals(50, (float) $seller->fresh()->balance, 'Balance is locked immediately on request.');

        app(PayoutService::class)->markPaid($payout, $admin);

        $payout->refresh();
        $this->assertEquals(PayoutStatus::Paid, $payout->status);
        $this->assertSame($admin->id, (int) $payout->processed_by);
        $this->assertNotNull($payout->processed_at);
        $this->assertEquals(50, (float) $seller->fresh()->balance, 'Final balance must be exactly $50.');

        $ledgerRow = Transaction::query()->where('payout_id', $payout->id)->firstOrFail();
        $this->assertEquals('-50.00', $ledgerRow->amount);
        $this->assertEquals('payout', $ledgerRow->type->value);
    }

    public function test_minimum_amount_is_enforced(): void
    {
        [$seller] = $this->sellerWithBalance(100);

        $this->expectException(ValidationException::class);
        app(PayoutService::class)->request($seller, 10);
    }

    public function test_rejection_returns_money_to_balance(): void
    {
        [$seller] = $this->sellerWithBalance(100);
        $admin = $this->makeAdmin();

        $payout = app(PayoutService::class)->request($seller, 50);
        $this->assertEquals(50, (float) $seller->fresh()->balance);

        app(PayoutService::class)->reject($payout, $admin, 'Invalid bank details');

        $this->assertEquals(PayoutStatus::Rejected, $payout->fresh()->status);
        $this->assertEquals(100, (float) $seller->fresh()->balance, 'Rejected payout returns funds.');
    }

    /** @return array{0: Seller} */
    protected function sellerWithBalance(float $amount): array
    {
        $seller = $this->makeApprovedSeller();
        Seller::query()->whereKey($seller->id)->update(['balance' => $amount]);

        return [$seller->fresh()];
    }
}
