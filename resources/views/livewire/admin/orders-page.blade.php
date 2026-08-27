<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-black text-gray-900">Manage Orders</h1>
        <select wire:model.live="status" class="rounded-lg border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
            <option value="">All statuses</option>
            @foreach ($statuses as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>
    </div>

    @if (session('success'))
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="mt-6 bg-white rounded-xl border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm min-w-[820px]">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-400">
                <tr><th class="px-5 py-3">Order</th><th class="px-5 py-3">Buyer</th><th class="px-5 py-3">Payment</th><th class="px-5 py-3">Total</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($orders as $order)
                    <tr wire:key="ao-{{ $order->id }}" class="hover:bg-gray-25">
                        <td class="px-5 py-3">
                            <p class="font-semibold">{{ $order->order_number }}</p>
                            <p class="text-[11px] text-gray-400">{{ $order->created_at->format('M d, Y H:i') }}</p>
                        </td>
                        <td class="px-5 py-3">{{ $order->user?->name }}</td>
                        <td class="px-5 py-3 capitalize text-xs">{{ str_replace('_', ' ', $order->payment_method->value) }}
                            @if ($order->payment_method === \App\Enums\PaymentMethod::ManualTransfer && $order->getMedia('payment_proof')->isNotEmpty())
                                @php $proof = $order->getFirstMedia('payment_proof'); @endphp
                                — <a href="{{ $proof->getUrl() }}" target="_blank" class="text-blue-600 font-semibold hover:underline">proof ↗</a>
                            @endif
                        </td>
                        <td class="px-5 py-3 font-bold">{{ money($order->total) }}</td>
                        <td class="px-5 py-3">{!! status_badge($order->status) !!}</td>
                        <td class="px-5 py-3 text-right space-x-2 whitespace-nowrap">
                            <button wire:click="$set('viewingId', {{ $order->id }})" class="text-orange-600 font-semibold hover:underline">Details</button>

                            @if ($order->status === \App\Enums\OrderStatus::PendingPayment && $order->payment_method === \App\Enums\PaymentMethod::ManualTransfer)
                                <button wire:click="verifyPayment({{ $order->id }})" wire:confirm="Confirm the bank transfer was received and mark this order PAID?"
                                        class="rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-emerald-500">Mark as Paid</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $orders->links() }}

    @if ($viewing)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-start justify-center p-4 sm:p-8" wire:key="modal">
            <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="font-black">Order {{ $viewing->order_number }} {!! status_badge($viewing->status) !!}</h2>
                    <button wire:click="$set('viewingId', null)" class="text-gray-400 hover:text-gray-700 text-xl leading-none">✕</button>
                </div>

                <div class="px-6 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
                    <div class="grid sm:grid-cols-2 gap-3 text-sm">
                        <div><p class="text-xs uppercase text-gray-400 font-bold">Buyer</p><p>{{ $viewing->user?->name }} · {{ $viewing->user?->email }}</p></div>
                        <div><p class="text-xs uppercase text-gray-400 font-bold">Paid at</p><p>{{ $viewing->paid_at?->format('M d, Y H:i') ?? '—' }}</p></div>
                        <div><p class="text-xs uppercase text-gray-400 font-bold">Ship to</p><p>{{ $viewing->shipping_name }}, {{ $viewing->shipping_city }} — {{ $viewing->shipping_address }}</p></div>
                        <div><p class="text-xs uppercase text-gray-400 font-bold">Phone</p><p>{{ $viewing->shipping_phone }}</p></div>
                    </div>

                    <table class="w-full text-sm">
                        <thead class="text-left text-xs uppercase text-gray-400"><tr><th class="py-2">Item</th><th class="py-2">Seller</th><th class="py-2 text-right">Subtotal</th><th class="py-2 text-right">Commission</th><th class="py-2 text-right">Earning</th></tr></thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($viewing->items as $item)
                                <tr>
                                    <td class="py-2.5">{{ $item->product_name }} ×{{ $item->quantity }}</td>
                                    <td class="py-2.5 text-gray-500">{{ $item->seller?->store_name }}</td>
                                    <td class="py-2.5 text-right">{{ money($item->subtotal) }}</td>
                                    <td class="py-2.5 text-right text-orange-600 font-medium">{{ money($item->commission_amount) }}</td>
                                    <td class="py-2.5 text-right text-emerald-600 font-medium">{{ money($item->seller_earning) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if ($viewing->payment_method === \App\Enums\PaymentMethod::ManualTransfer && $viewing->getMedia('payment_proof')->isNotEmpty())
                        <div>
                            <p class="text-xs uppercase text-gray-400 font-bold mb-1">Transfer proof</p>
                            <img src="{{ $viewing->getFirstMediaUrl('payment_proof') }}" alt="proof" class="max-h-64 rounded-lg border border-gray-200" />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
