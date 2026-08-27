<?php

namespace Tests\Feature;

use App\Enums\SellerStatus;
use App\Livewire\Admin\SellersPage;
use App\Livewire\Sell\SellerApplicationPage;
use App\Models\Seller;
use App\Notifications\SellerApprovedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\MarketplaceTesting;
use Tests\TestCase;

/**
 * QA TEST 1 — SELLER ONBOARDING
 * Register -> Apply Seller -> Admin Approve -> Seller can access /seller.
 */
class Qa1SellerOnboardingTest extends TestCase
{
    use MarketplaceTesting, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBase();
    }

    public function test_buyer_applies_admin_approves_and_seller_dashboard_opens(): void
    {
        $buyer = $this->makeBuyer();
        $admin = $this->makeAdmin();

        $this->actingAs($buyer)
            ->get('/become-a-seller')
            ->assertOk();

        Livewire::test(SellerApplicationPage::class)
            ->set('store_name', 'Ahmed Electronics')
            ->set('phone', '+961 3 111 222')
            ->set('description', 'We sell phones and electronics across Lebanon with fast delivery.')
            ->call('submit')
            ->assertHasNoErrors();

        $seller = Seller::query()->where('store_name', 'Ahmed Electronics')->firstOrFail();

        $this->assertTrue($seller->status === SellerStatus::Pending);
        $this->assertFalse($buyer->hasRole('seller'));

        $this->actingAs($admin);
        Livewire::test(SellersPage::class)->call('approve', $seller->id);

        $seller->refresh();
        $this->assertTrue($seller->fresh()->status === SellerStatus::Approved);
        $this->assertTrue($buyer->fresh()->hasRole('seller'));

        $this->assertDatabaseHas('notifications', [
            'type' => SellerApprovedNotification::class,
            'notifiable_id' => $buyer->id,
        ]);

        $this->actingAs($buyer->fresh())
            ->get('/seller')
            ->assertOk();
    }

    public function test_rejected_applicant_cannot_access_seller_dashboard(): void
    {
        $applicant = $this->makeBuyer();
        $admin = $this->makeAdmin();

        $this->actingAs($applicant)
            ->get('/become-a-seller')->assertOk();

        Livewire::test(SellerApplicationPage::class)
            ->set('store_name', 'Shady Store')
            ->set('phone', '+961 3 999 999')
            ->set('description', 'Suspicious description for the shady store here.')
            ->call('submit');

        $seller = Seller::query()->where('store_name', 'Shady Store')->firstOrFail();

        $this->actingAs($admin);
        Livewire::test(SellersPage::class)->call('reject', $seller->id, 'Verification failed.');

        $this->assertTrue($seller->fresh()->status === SellerStatus::Rejected);
        $this->assertFalse($applicant->hasRole('seller'));

        $this->actingAs($applicant)
            ->get('/seller')
            ->assertForbidden();
    }
}
