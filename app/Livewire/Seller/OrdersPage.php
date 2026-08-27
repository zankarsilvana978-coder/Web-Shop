<?php

namespace App\Livewire\Seller;

use App\Enums\OrderItemStatus;
use App\Models\OrderItem;
use App\Services\ShippingStateService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class OrdersPage extends Component
{
    use WithPagination;

    public string $status = '';

    public bool $showShipForm = false;

    public ?int $shippingItemId = null;

    public string $carrier = '';

    public string $tracking_number = '';

    protected function rules(): array
    {
        return [
            'carrier' => 'nullable|string|max:100',
            'tracking_number' => 'required|string|min:4|max:100',
        ];
    }

    public function openShipForm(int $itemId): void
    {
        $this->shippingItemId = $itemId;
        $this->resetErrorBag();
        $this->showShipForm = true;
    }

    /** Policy-checked: only the owning seller can ship. */
    public function markShipped(ShippingStateService $shipping): void
    {
        $item = OrderItem::query()->findOrFail($this->shippingItemId);

        $this->authorize('ship', $item);

        $data = $this->validate();

        $shipping->markShipped($item, $data['carrier'] ?: null, $data['tracking_number']);

        $this->showShipForm = false;
        session()->flash('success', 'Order marked as shipped. The buyer has been notified.');
    }

    public function render()
    {
        return view('livewire.seller.orders-page', [
            'items' => OrderItem::query()
                ->where('seller_id', auth()->user()->seller->id)
                ->with(['order:id,order_number,status,paid_at,user_id', 'order.user:id,name'])
                ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
                ->latest()
                ->paginate(10),
            'statuses' => OrderItemStatus::cases(),
        ]);
    }
}
