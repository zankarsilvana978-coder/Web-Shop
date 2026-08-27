<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('orders.index') }}" wire:navigate class="text-sm text-orange-600 font-medium hover:underline">← All orders</a>

    <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-2xl font-black text-gray-900">Order {{ $order->order_number }}</h1>
        <div class="flex items-center gap-3">
            <span class="font-black text-xl">{{ money($order->total) }}</span>
            {!! status_badge($order->status) !!}
        </div>
    </div>

    <div class="mt-4 bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-xs font-bold uppercase tracking-wide text-gray-400">Payment</h2>
        <p class="mt-2 text-sm text-gray-700">
            {{ $order->payment_method->label() }}

            @if ($order->status === \App\Enums\OrderStatus::PendingPayment)
                @if ($order->payment_method === \App\Enums\PaymentMethod::ManualTransfer)
                    — Transfer {{ money($order->total) }} to our account (IBAN: LB00 0000 0000 0000 0000 0000 0000, Soukelkom SAL) and send the receipt to support. Your order activates after verification.
                @endif
            @elseif ($order->paid_at)
                — Paid {{ $order->paid_at->format('M d, Y H:i') }}
            @endif
        </p>
    </div>

    <div class="mt-4 bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-xs font-bold uppercase tracking-wide text-gray-400">Shipping to</h2>
        <p class="mt-2 text-sm text-gray-700">{{ $order->shipping_name }} · {{ $order->shipping_phone }}</p>
        <p class="text-sm text-gray-700">{{ $order->shipping_city }}, {{ $order->shipping_address }}</p>
    </div>

    <h2 class="mt-6 mb-3 font-black text-gray-900">Items</h2>

    <div class="space-y-3">
        @foreach ($items as $item)
            <div class="bg-white rounded-xl border border-gray-200 p-5" wire:key="oi-{{ $item->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $item->product_name }} <span class="text-gray-400 font-normal">×{{ $item->quantity }}</span></p>
                        <p class="text-xs text-gray-500 mt-0.5">Sold by {{ $item->seller->store_name }}</p>

                        @if ($item->tracking_number)
                            <p class="mt-1 text-xs font-medium text-blue-700">
                                Tracking: {{ $item->tracking_number }}
                                @if ($item->carrier) · {{ $item->carrier }} @endif
                                @if ($item->shipped_at) · shipped {{ $item->shipped_at->format('M d') }} @endif
                            </p>
                        @endif

                        @if ($item->status === \App\Enums\OrderItemStatus::Cancelled && $item->cancellation_reason)
                            <p class="mt-1 text-xs text-red-600">{{ $item->cancellation_reason }}</p>
                        @endif
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        <span class="font-bold">{{ money($item->subtotal) }}</span>
                        {!! status_badge($item->status) !!}

                        @if ($item->status === \App\Enums\OrderItemStatus::Shipped)
                            <button wire:click="confirmDelivery({{ $item->id }})"
                                    class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-500">
                                Confirm delivery
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
