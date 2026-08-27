<?php

namespace App\Services;

use App\Enums\PayoutStatus;
use App\Enums\TransactionType;
use App\Models\Payout;
use App\Models\Seller;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\AdminPayoutRequestedNotification;
use App\Notifications\SellerPayoutProcessedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class PayoutService
{
    /**
     * Seller requests a payout. The balance is deducted immediately
     * (held by the pending payout) to prevent double-spending.
     */
    public function request(Seller $seller, float $amount, ?string $bankDetails = null): Payout
    {
        $minimum = (float) Setting::get('payout_min');

        if ($amount < $minimum) {
            throw ValidationException::withMessages([
                'amount' => "Minimum payout is \${$minimum}.",
            ]);
        }

        return DB::transaction(function () use ($seller, $amount, $bankDetails) {
            $fresh = Seller::query()->lockForUpdate()->findOrFail($seller->id);

            if ((float) $fresh->balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Requested amount exceeds your available balance.',
                ]);
            }

            $payout = null;

            $fresh->decrement('balance', round($amount, 2));
            $payout = $fresh->payouts()->create([
                'amount' => round($amount, 2),
                'method' => 'bank_transfer',
                'bank_details' => $bankDetails,
                'status' => PayoutStatus::Pending,
            ]);

            Notification::send(
                User::role('admin')->get(),
                new AdminPayoutRequestedNotification($payout),
            );

            return $payout;
        });
    }

    /** Admin confirms the bank transfer went out; ledger + seller email. */
    public function markPaid(Payout $payout, User $admin): Payout
    {
        return DB::transaction(function () use ($payout, $admin) {
            $fresh = Payout::query()->lockForUpdate()->findOrFail($payout->id);

            if ($fresh->status !== PayoutStatus::Pending) {
                return $fresh;
            }

            $fresh->forceFill([
                'status' => PayoutStatus::Paid,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ])->save();

            $fresh->seller->transactions()->create([
                'payout_id' => $fresh->id,
                'type' => TransactionType::Payout,
                'amount' => -abs((float) $fresh->amount),
                'description' => "Payout #{$fresh->id} sent via bank transfer",
            ]);

            $fresh->seller->user->notify(new SellerPayoutProcessedNotification($fresh));

            return $fresh;
        });
    }

    /** Admin rejects the payout; money returns to the available balance. */
    public function reject(Payout $payout, User $admin, string $note): Payout
    {
        return DB::transaction(function () use ($payout, $admin, $note) {
            $fresh = Payout::query()->lockForUpdate()->findOrFail($payout->id);

            if ($fresh->status !== PayoutStatus::Pending) {
                return $fresh;
            }

            $fresh->forceFill([
                'status' => PayoutStatus::Rejected,
                'admin_note' => $note,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ])->save();

            $fresh->seller->increment('balance', (float) $fresh->amount);

            $fresh->seller->user->notify(new SellerPayoutProcessedNotification($fresh));

            return $fresh;
        });
    }
}
