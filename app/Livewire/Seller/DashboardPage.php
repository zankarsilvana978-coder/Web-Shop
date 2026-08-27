<?php

namespace App\Livewire\Seller;

use App\Enums\OrderItemStatus;
use App\Enums\ProductStatus;
use App\Enums\TransactionType;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DashboardPage extends Component
{
    public function render()
    {
        $seller = auth()->user()->seller;
        $now = Carbon::now();

        $monthSales = $seller->orderItems()
            ->whereHas('order', fn ($q) => $q->paid())
            ->where('created_at', '>=', $now->copy()->startOfMonth())
            ->sum('subtotal');

        $commissionPaid = $seller->transactions()
            ->where('type', TransactionType::Commission)
            ->sum('amount');

        $awaitingShipment = $seller->orderItems()->where('status', OrderItemStatus::AwaitingShipment)->count();

        return view('livewire.seller.dashboard-page', [
            'seller' => $seller,
            'kpis' => [
                'balance' => (float) $seller->balance,
                'pending_balance' => (float) $seller->pending_balance,
                'month_sales' => round((float) $monthSales, 2),
                'commission_paid' => round((float) $commissionPaid, 2),
                'products_count' => $seller->products()->count(),
                'active_products' => $seller->products()->where('status', ProductStatus::Active)->count(),
                'awaiting_shipment' => $awaitingShipment,
            ],
            'recentItems' => OrderItem::query()
                ->where('seller_id', $seller->id)
                ->with(['order:id,order_number,status,paid_at,user_id', 'order.user:id,name'])
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
