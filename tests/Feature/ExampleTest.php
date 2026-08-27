<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MarketplaceTesting;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use MarketplaceTesting, RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seedBase();

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
