<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Livewire\Admin\ProductsModerationPage;
use App\Livewire\Seller\ProductsPage;
use App\Livewire\Storefront\ProductBrowser;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\MarketplaceTesting;
use Tests\TestCase;

/**
 * QA TEST 2 — PRODUCT LIFECYCLE
 * Seller creates product -> Admin approves -> Product appears on homepage.
 */
class Qa2ProductLifecycleTest extends TestCase
{
    use MarketplaceTesting, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBase();
    }

    public function test_submitted_product_requires_approval_then_goes_live(): void
    {
        $seller = $this->makeApprovedSeller();
        $admin = $this->makeAdmin();

        $this->actingAs($seller->user);

        Livewire::test(ProductsPage::class)
            ->call('createNew')
            ->set('name', 'iPhone 15 128GB')
            ->set('description', 'Brand new sealed iPhone 15 with official warranty.')
            ->set('price', 1000)
            ->set('stock', 10)
            ->set('sku', 'IP15-128-BLK')
            ->set('category_id', Category::factory()->create()->id)
            ->call('save', true)
            ->assertHasNoErrors();

        $product = Product::query()->where('sku', 'IP15-128-BLK')->firstOrFail();

        $this->assertEquals(ProductStatus::PendingReview, $product->status);

        $this->actingAs($admin);
        Livewire::test(ProductsModerationPage::class)->call('approve', $product->id);

        $this->assertEquals(ProductStatus::Active, $product->fresh()->status);

        $homepage = Livewire::test(ProductBrowser::class);
        $homepage->assertSee('iPhone 15 128GB');
    }

    public function test_rejected_product_gets_reason_and_stays_hidden(): void
    {
        $seller = $this->makeApprovedSeller();
        $admin = $this->makeAdmin();

        $product = $this->makeActiveProduct($seller, 25);
        $product->update(['status' => ProductStatus::PendingReview]);

        $this->actingAs($admin);
        Livewire::test(ProductsModerationPage::class)
            ->call('reject', $product->id, 'No product image provided.');

        $this->assertEquals(ProductStatus::Rejected, $product->fresh()->status);
        $this->assertSame('No product image provided.', $product->fresh()->rejection_reason);

        Livewire::test(ProductBrowser::class)->assertDontSee($product->name);
    }
}
