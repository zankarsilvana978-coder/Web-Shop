<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-black text-gray-900">My Orders</h1>

    <div class="mt-6 space-y-4">
        @forelse ($orders as $order)
            <a href="{{ route('orders.show', $order) }}" wire:navigate
               class="block bg-white rounded-xl border border-gray-200 p-5 hover:border-orange-300 hover:shadow-sm transition">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="font-bold text-gray-900">{{ $order->order_number }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $order->created_at->format('M d, Y H:i') }} · {{ $order->items_count }} item(s)</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-black text-gray-900">{{ money($order->total) }}</span>
                        {!! status_badge($order->status) !!}
                    </div>
                </div>

                @if ($tracking = $order->items->firstWhere('status', \App\Enums\OrderItemStatus::Shipped)?->tracking_number)
                    <p class="mt-2 text-xs text-blue-700 font-medium">In transit — tracking: {{ $tracking }}</p>
                @endif
            </a>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-10 text-center">
                <p class="text-gray-500">You haven't placed any orders yet.</p>
                <a href="{{ route('home') }}" wire:navigate class="text-orange-600 font-semibold hover:underline">Start shopping</a>
            </div>
        @endforelse
    </div>

    {{ $orders->links() }}
</div>
