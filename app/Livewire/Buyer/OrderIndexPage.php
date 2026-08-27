<?php

namespace App\Livewire\Buyer;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class OrderIndexPage extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.buyer.order-index-page', [
            'orders' => Order::query()
                ->where('user_id', auth()->id())
                ->with(['items' => fn ($q) => $q->select('id', 'order_id', 'status', 'tracking_number')])
                ->withCount('items')
                ->latest()
                ->paginate(10),
        ]);
    }
}
