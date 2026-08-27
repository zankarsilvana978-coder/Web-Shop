<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\Transaction;
use App\Notifications\BuyerOrderShippedNotification;
use Illuminate\Support\Facades\DB;

class ShippingStateService
{
    /** Seller ships their slice: 48h clock, tracking number required. */
    public function markShipped(OrderItem $item, ?string $carrier, string $trackingNumber): OrderItem
    {
        return DB::transaction(function () use ($item, $carrier, $trackingNumber) {
            $fresh = OrderItem::query()->lockForUpdate()->findOrFail($item->getKey());

            if ($fresh->status !== OrderItemStatus::AwaitingShipment) {
                abort(422, 'This item cannot be shipped.');
            }

            $fresh->forceFill([
                'status' => OrderItemStatus::Shipped,
                'carrier' => $carrier,
                'tracking_number' => $trackingNumber,
                'shipped_at' => now(),
            ])->save();

            $fresh->order->user->notify(new BuyerOrderShippedNotification($fresh));

            return $fresh;
        });
    }

    /**
     * Delivery confirmed (buyer or admin). Starts the earning hold
     * countdown: available_at = delivered_at + hold days.
     */
    public function markDelivered(OrderItem $item): OrderItem
    {
        return DB::transaction(function () use ($item) {
            $fresh = OrderItem::query()->lockForUpdate()->findOrFail($item->getKey());

            if ($fresh->status !== OrderItemStatus::Shipped) {
                abort(422, 'This item is not in transit.');
            }

            $holdDays = (int) Setting::get('earning_hold_days');

            $fresh->forceFill([
                'status' => OrderItemStatus::Delivered,
                'delivered_at' => now(),
            ])->save();

            Transaction::query()
                ->where('order_item_id', $fresh->id)
                ->where('type', TransactionType::Earning)
                ->whereNull('available_at')
                ->update(['available_at' => now()->addDays($holdDays)]);

            $this->completeOrderIfDone($fresh->order);

            return $fresh;
        });
    }

    protected function completeOrderIfDone(Order $order): void
    {
        $order->refresh();

        if ($order->allItemsDelivered() && $order->status === OrderStatus::Paid) {
            $order->forceFill([
                'status' => OrderStatus::Completed,
                'completed_at' => now(),
            ])->save();
        }
    }
}
