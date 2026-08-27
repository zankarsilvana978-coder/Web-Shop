<?php

namespace App\Notifications;

use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Seller $seller,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Update on your seller application')
            ->greeting("Hello {$notifiable->name},")
            ->line('Unfortunately your seller application was not approved at this time.')
            ->when($this->seller->rejection_reason, fn ($mail) => $mail->line('Reason: '.$this->seller->rejection_reason))
            ->line('You may contact support for more details.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Your seller application was rejected',
            'seller_id' => $this->seller->id,
        ];
    }
}
