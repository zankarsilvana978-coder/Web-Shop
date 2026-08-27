<?php

namespace App\Livewire\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\PaymentService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class OrdersPage extends Component
{
    use WithPagination;

    public string $status = '';

    public ?int $viewingId = null;

    public function verifyPayment(int $orderId, PaymentService $payments): void
    {
        $order = Order::findOrFail($orderId);

        $payments->verifyManualPayment($order, auth()->user());

        session()->flash('success', "Order {$order->order_number} marked as paid. Earnings distribution queued.");
    }

    public function render()
    {
        return view('livewire.admin.orders-page', [
            'orders' => Order::query()
                ->with(['user:id,name,email', 'items' => fn ($q) => $q->select('id', 'order_id', 'seller_id', 'product_name', 'quantity', 'subtotal', 'commission_amount', 'status')])
                ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
                ->latest()
                ->paginate(10),
            'statuses' => OrderStatus::cases(),
            'viewing' => $this->viewingId ? Order::with(['items.seller:id,store_name', 'user', 'media'])->find($this->viewingId) : null,
        ]);
    }
}
