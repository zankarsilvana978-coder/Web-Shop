<?php

namespace App\Livewire\Admin;

use App\Enums\PayoutStatus;
use App\Enums\ProductStatus;
use App\Enums\SellerStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payout;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DashboardPage extends Component
{
    public function render()
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();

        return view('livewire.admin.dashboard-page', [
            'kpis' => [
                'total_commission' => round((float) OrderItem::query()
                    ->whereHas('order', fn ($q) => $q->paid())
                    ->sum('commission_amount'), 2),
                'month_commission' => round((float) OrderItem::query()
                    ->whereHas('order', fn ($q) => $q->paid())
                    ->where('created_at', '>=', $monthStart)
                    ->sum('commission_amount'), 2),
                'active_sellers' => Seller::query()->where('status', SellerStatus::Approved)->count(),
                'pending_sellers' => Seller::query()->pending()->count(),
                'pending_products' => Product::query()->where('status', ProductStatus::PendingReview)->count(),
                'pending_payouts_count' => Payout::query()->where('status', PayoutStatus::Pending)->count(),
                'pending_payouts_amount' => round((float) Payout::query()->where('status', PayoutStatus::Pending)->sum('amount'), 2),
                'orders_total' => Order::paid()->count(),
            ],
            'recentOrders' => Order::query()->with('user:id,name')->latest()->take(8)->get(),
        ]);
    }
}
