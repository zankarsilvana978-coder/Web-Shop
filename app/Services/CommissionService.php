<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Seller;
use App\Models\Setting;

class CommissionService
{
    /**
     * Resolution order: per-product override -> per-seller override -> global.
     */
    public function resolveRate(?float $productRate, ?float $sellerRate): float
    {
        $rate = $productRate ?? $sellerRate ?? Setting::get('global_commission_rate');

        return min(100.0, max(0.0, (float) $rate));
    }

    public function rateFor(Product $product): float
    {
        return $this->resolveRate(
            $product->commission_rate !== null ? (float) $product->commission_rate : null,
            $product->seller->commission_rate !== null ? (float) $product->seller->commission_rate : null,
        );
    }

    /**
     * Split a subtotal into platform commission and seller earning.
     *
     * @return array{commission: float, earning: float}
     */
    public function calculate(float $subtotal, float $ratePercent): array
    {
        $commission = round($subtotal * $ratePercent / 100, 2);

        return [
            'commission' => $commission,
            'earning' => round($subtotal - $commission, 2),
        ];
    }

    public function splitFor(Product $product, float $subtotal): array
    {
        return $this->calculate($subtotal, $this->rateFor($product));
    }
}
