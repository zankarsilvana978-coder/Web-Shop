<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerNewPaidOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, OrderItem>  $items  This seller's slice of the order
     */
    public function __construct(
        public Order $order,
        public Seller $seller,
        public array $items,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $earning = collect($this->items)->sum(fn ($i) => (float) $i->seller_earning);

        return (new MailMessage)
            ->subject('You have a new order — ship within 48h')
            ->greeting("Congratulations {$this->seller->store_name}!")
            ->line("A buyer paid for your products in order {$this->order->order_number}.")
            ->line('Your earning: $'.number_format($earning, 2))
            ->line('Please mark the order as shipped within 48 hours with a tracking number.')
            ->action('Open my orders', route('seller.orders'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "New paid order {$this->order->order_number}",
            'order_id' => $this->order->id,
            'items' => count($this->items),
        ];
    }
}
