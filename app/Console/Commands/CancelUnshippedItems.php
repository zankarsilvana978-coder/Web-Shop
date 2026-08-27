<?php

namespace App\Console\Commands;

use App\Enums\OrderItemStatus;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Services\EarningsService;
use Illuminate\Console\Command;

/**
 * Ship-by-seller SLA: any item still unshipped past the deadline
 * (default 48h after payment) auto-cancels and the buyer is refunded.
 */
class CancelUnshippedItems extends Command
{
    protected $signature = 'soukelkom:cancel-unshipped-items';

    protected $description = 'Auto-cancel order items whose seller missed the shipping deadline and refund them';

    public function handle(EarningsService $earnings): int
    {
        $deadlineHours = (int) Setting::get('ship_deadline_hours');
        $cutoff = now()->subHours($deadlineHours);

        $items = OrderItem::query()
            ->where('status', OrderItemStatus::AwaitingShipment)
            ->whereHas('order', fn ($q) => $q->paid()->where('paid_at', '<=', $cutoff))
            ->with('order')
            ->get();

        foreach ($items as $item) {
            $earnings->refundItem(
                $item,
                "Seller did not ship within {$deadlineHours} hours — buyer refunded.",
                OrderItemStatus::Cancelled,
            );
        }

        $this->info("Cancelled {$items->count()} unshipped item(s).");

        return self::SUCCESS;
    }
}
