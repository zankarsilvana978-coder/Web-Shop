<?php

namespace App\Notifications;

use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminPayoutRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Payout $payout,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Payout request: {$this->payout->seller->store_name} — $".number_format((float) $this->payout->amount, 2),
            'payout_id' => $this->payout->id,
        ];
    }
}
