<?php

namespace App\Livewire\Buyer;

use App\Enums\OrderItemStatus;
use App\Models\Order;
use App\Services\ShippingStateService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class OrderDetailPage extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 403);
        $this->order = $order;
    }

    /** Buyer confirms receipt; starts the seller earning hold clock. */
    public function confirmDelivery(ShippingStateService $shipping, int $itemId): void
    {
        $item = $this->order->items()->findOrFail($itemId);

        abort_unless($item->status === OrderItemStatus::Shipped, 422, 'Item is not in transit.');

        $shipping->markDelivered($item);

        $this->order->refresh();
        session()->flash('success', 'Thanks for confirming delivery!');
    }

    public function render()
    {
        return view('livewire.buyer.order-detail-page', [
            'order' => $this->order->load(['items.seller', 'items.product.media']),
        ]);
    }
}
