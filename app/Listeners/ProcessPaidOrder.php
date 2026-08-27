<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Jobs\DistributeEarnings;
use App\Models\User;
use App\Notifications\AdminOrderPaidNotification;
use App\Notifications\SellerNewPaidOrderNotification;
use Illuminate\Support\Facades\Notification;

class ProcessPaidOrder
{
    /**
     * Single entry point after payment confirmation:
     * queue the money split, then alert admins and each seller.
     */
    public function handle(OrderPaid $event): void
    {
        $order = $event->order->refresh();

        DistributeEarnings::dispatch($order);

        Notification::send(
            User::role('admin')->get(),
            new AdminOrderPaidNotification($order),
        );

        $order->items->groupBy('seller_id')->each(function ($items, $sellerId) use ($order) {
            $seller = $items->first()->seller;

            if ($seller && $seller->user) {
                $seller->user->notify(new SellerNewPaidOrderNotification($order, $seller, $items->all()));
            }
        });
    }
}
