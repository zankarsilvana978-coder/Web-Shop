<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\EarningsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Triggered after payment is confirmed. Loops the order items and
 * credits each seller's pending balance + writes the ledger rows.
 */
class DistributeEarnings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Order $order,
    ) {}

    public function handle(EarningsService $earnings): void
    {
        $earnings->distribute($this->order);
    }
}
