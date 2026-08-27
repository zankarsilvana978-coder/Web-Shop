<?php

namespace App\Notifications;

use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerApprovedNotification extends Notification implements ShouldQueue
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
            ->subject('Congratulations — your store is approved!')
            ->greeting("Congratulations, {$this->seller->store_name} is live!")
            ->line('Your seller application has been approved. You can now list products and start selling on Soukelkom.')
            ->line('Tip: products go live after a quick admin review.')
            ->action('Open my seller dashboard', route('seller.dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Your store was approved',
            'seller_id' => $this->seller->id,
        ];
    }
}
