<?php

namespace App\Notifications;

use App\Enums\PayoutStatus;
use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerPayoutProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Payout $payout,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->greeting("Hello {$notifiable->name},");

        if ($this->payout->status === PayoutStatus::Paid) {
            return $mail->subject('Payout of $'.number_format((float) $this->payout->amount, 2).' sent')
                ->line('Your payout of $'.number_format((float) $this->payout->amount, 2).' has been transferred to your bank account.')
                ->line('It should appear within 1-3 business days.');
        }

        return $mail->subject('Your payout request was rejected')
            ->line('Your payout request of $'.number_format((float) $this->payout->amount, 2).' was rejected.')
            ->line('Reason: '.($this->payout->admin_note ?? 'Not specified.'))
            ->line('The amount has been returned to your available balance.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Payout #{$this->payout->id} {$this->payout->status->label()}",
            'payout_id' => $this->payout->id,
            'amount' => $this->payout->amount,
        ];
    }
}
