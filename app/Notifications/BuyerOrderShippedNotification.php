<?php

namespace App\Notifications;

use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BuyerOrderShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public OrderItem $item,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tracking = $this->item->tracking_number;

        return (new MailMessage)
            ->subject("Your order {$this->item->order->order_number} has shipped")
            ->greeting("Good news, {$notifiable->name}!")
            ->line("Your item '{$this->item->product_name}' is on its way.")
            ->when($tracking, fn ($mail) => $mail->line("Tracking number: {$tracking}".($this->item->carrier ? " ({$this->item->carrier})" : '')))
            ->action('View my order', route('orders.show', $this->item->order));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Order {$this->item->order->order_number} shipped",
            'order_id' => $this->item->order_id,
            'tracking_number' => $this->item->tracking_number,
        ];
    }
}
