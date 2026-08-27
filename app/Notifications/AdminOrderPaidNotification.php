<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminOrderPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New paid order — {$this->order->order_number}")
            ->greeting('New paid order on Soukelkom')
            ->line("Order {$this->order->order_number} was paid: \$".number_format((float) $this->order->total, 2))
            ->line('Commission and earnings distribution has been queued.')
            ->action('Open admin orders', route('admin.orders'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "New paid order {$this->order->order_number}",
            'order_id' => $this->order->id,
            'total' => $this->order->total,
        ];
    }
}
