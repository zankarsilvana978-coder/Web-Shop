<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class EarningsService
{
    /**
     * Loop order items, write ledger rows and credit each seller's
     * pending balance. Idempotent: items already distributed are skipped.
     */
    public function distribute(Order $order): void
    {
        $order->loadMissing('items.seller');

        foreach ($order->items as $item) {
            DB::transaction(function () use ($item) {
                $alreadyDistributed = $item->transactions()
                    ->whereIn('type', [TransactionType::Earning->value, TransactionType::Commission->value])
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyDistributed) {
                    return;
                }

                $description = "Order {$item->order->order_number} — {$item->product_name} x{$item->quantity}";

                $item->seller->transactions()->create([
                    'order_item_id' => $item->id,
                    'type' => TransactionType::Earning,
                    'amount' => $item->seller_earning,
                    'description' => $description,
                ]);

                $item->seller->transactions()->create([
                    'order_item_id' => $item->id,
                    'type' => TransactionType::Commission,
                    'amount' => $item->commission_amount,
                    'description' => "Platform commission — {$description}",
                ]);

                $item->seller->increment('pending_balance', (float) $item->seller_earning);
            });
        }
    }

    /**
     * Cancel/refund one item: restock, reverse the held earning in the
     * ledger, then recompute the parent order status.
     */
    public function refundItem(OrderItem $item, string $reason, OrderItemStatus $finalStatus = OrderItemStatus::Cancelled): bool
    {
        return DB::transaction(function () use ($item, $reason, $finalStatus): bool {
            $fresh = OrderItem::query()->lockForUpdate()->findOrFail($item->getKey());

            if (in_array($fresh->status, [OrderItemStatus::Cancelled, OrderItemStatus::Refunded], true)) {
                return false;
            }

            if ($fresh->product_id && $fresh->status === OrderItemStatus::AwaitingShipment) {
                Product::query()->whereKey($fresh->product_id)->increment('stock', $fresh->quantity);
            }

            $earningTxn = $fresh->transactions()->where('type', TransactionType::Earning)->first();

            if ($earningTxn && is_null($earningTxn->released_at)) {
                $fresh->seller->transactions()->create([
                    'order_item_id' => $fresh->id,
                    'type' => TransactionType::Refund,
                    'amount' => -abs((float) $fresh->seller_earning),
                    'description' => "Refund — {$reason}",
                ]);

                $fresh->seller->decrement('pending_balance', (float) $fresh->seller_earning);
            }

            $fresh->forceFill([
                'status' => $finalStatus,
                'cancellation_reason' => $reason,
            ])->save();

            $this->recomputeOrderStatus($fresh->order);

            return true;
        });
    }

    /**
     * Release every earning whose hold window has elapsed:
     * pending_balance -> balance, ready for payout requests.
     */
    public function releaseDue(): array
    {
        return DB::transaction(function (): array {
            $due = Transaction::query()
                ->releasable()
                ->lockForUpdate()
                ->get();

            $bySeller = [];

            foreach ($due as $txn) {
                $txn->forceFill(['released_at' => now()])->save();
                $bySeller[$txn->seller_id] = ($bySeller[$txn->seller_id] ?? 0.0) + (float) $txn->amount;
            }

            foreach ($bySeller as $sellerId => $amount) {
                $seller = Seller::find($sellerId);
                if (! $seller) {
                    continue;
                }
                $seller->increment('balance', round($amount, 2));
                $seller->decrement('pending_balance', round($amount, 2));
            }

            return [
                'count' => $due->count(),
                'total' => round(array_sum($bySeller), 2),
            ];
        });
    }

    protected function recomputeOrderStatus(Order $order): void
    {
        $order->refresh();

        $activeItems = $order->items()
            ->whereNotIn('status', [OrderItemStatus::Cancelled->value, OrderItemStatus::Refunded->value])
            ->count();

        if ($activeItems === 0) {
            $order->forceFill(['status' => OrderStatus::Refunded])->save();
        }
    }
}
