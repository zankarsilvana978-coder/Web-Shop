<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MarketplaceTesting;
use Tests\TestCase;

class Qa8CommissionServiceTest extends TestCase
{
    use MarketplaceTesting, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBase();
    }

    public function test_spec_example_splits_correctly(): void
    {
        $service = app(CommissionService::class);

        $split50 = $service->calculate(50.00, 10);
        $split20 = $service->calculate(20.00, 10);

        $this->assertSame(['commission' => 5.0, 'earning' => 45.0], $split50);
        $this->assertSame(['commission' => 2.0, 'earning' => 18.0], $split20);

        $totalCommission = $split50['commission'] + $split20['commission'];
        $this->assertEquals(7.0, $totalCommission, 'Platform total commission must be $7.');
    }

    public function test_rate_precedence_product_over_seller_over_global(): void
    {
        $service = app(CommissionService::class);
        $seller = $this->makeApprovedSeller();

        $product = Product::factory()->for($seller)->create();
        $this->assertEquals(10.0, $service->rateFor($product), 'Global rate applies when no override exists.');

        $seller->update(['commission_rate' => 8]);
        $product->refresh();
        $this->assertEquals(8.0, $service->rateFor($product), 'Seller override beats global.');

        $product->update(['commission_rate' => 5]);
        $product->refresh();
        $this->assertEquals(5.0, $service->rateFor($product), 'Product override beats everything.');
    }
}
