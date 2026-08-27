<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\OrderPaid;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Idempotently flip an order to paid, then trigger the earnings
     * distribution chain (emails + queued job) exactly once.
     */
    public function markOrderPaid(Order $order, ?string $paymentIntentId = null): Order
    {
        $updated = DB::transaction(function () use ($order, $paymentIntentId) {
            $fresh = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if ($fresh->status !== OrderStatus::PendingPayment) {
                return false;
            }

            $fresh->forceFill([
                'status' => OrderStatus::Paid,
                'paid_at' => now(),
                'stripe_payment_intent_id' => $paymentIntentId ?? $fresh->stripe_payment_intent_id,
            ])->save();

            return true;
        });

        if (! $updated) {
            return $order->refresh();
        }

        event(new OrderPaid($order->refresh()));

        return $order->refresh();
    }

    /** Admin verifies a manual bank-transfer payment. */
    public function verifyManualPayment(Order $order, User $admin): Order
    {
        abort_if($order->payment_method !== PaymentMethod::ManualTransfer, 422, 'Order is not a manual transfer.');

        return $this->markOrderPaid($order);
    }

    public static function stripeEnabled(): bool
    {
        return filled(config('services.stripe.secret'));
    }
}
