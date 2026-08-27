<?php

namespace App\Notifications;

use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SellerApplicationSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Seller $seller,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "New seller application: {$this->seller->store_name}",
            'seller_id' => $this->seller->id,
        ];
    }
}
