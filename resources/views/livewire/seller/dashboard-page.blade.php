<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900">Seller Hub</h1>
                <p class="text-sm text-gray-500">{{ $seller->store_name }}</p>
            </div>

            <nav class="flex flex-wrap gap-1 bg-white rounded-lg border border-gray-200 p-1">
                @foreach ([
                    'dashboard' => 'Dashboard',
                    'products' => 'My Products',
                    'orders' => 'My Orders',
                    'payouts' => 'Payouts',
                    'settings' => 'Store Settings',
                ] as $route => $label)
                    <a href="{{ route("seller.$route") }}" wire:current.exact="seller.{{ $route }}"
                       class="rounded-md px-3 py-1.5 text-sm font-medium {{ request()->routeIs("seller.$route") ? 'bg-orange-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Available balance</p>
                <p class="mt-2 text-2xl font-black text-emerald-600">{{ money($kpis['balance']) }}</p>
                <p class="text-[11px] text-gray-400 mt-1">{{ money($kpis['pending_balance']) }} on hold (14-day protection)</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">This month sales</p>
                <p class="mt-2 text-2xl font-black text-gray-900">{{ money($kpis['month_sales']) }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Total commission paid</p>
                <p class="mt-2 text-2xl font-black text-orange-600">{{ money($kpis['commission_paid']) }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Products</p>
                <p class="mt-2 text-2xl font-black text-gray-900">{{ $kpis['active_products'] }}<span class="text-base font-semibold text-gray-400">/{{ $kpis['products_count'] }}</span></p>
                <p class="text-[11px] text-gray-400 mt-1">active / total</p>
            </div>
        </div>

        @if ($kpis['awaiting_shipment'] > 0)
            <a href="{{ route('seller.orders') }}" wire:navigate
               class="mt-4 block rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 hover:bg-amber-100 transition">
                <p class="font-bold text-amber-800">⏰ {{ $kpis['awaiting_shipment'] }} item(s) waiting to be shipped</p>
                <p class="text-sm text-amber-700 mt-0.5">Ship within 48 hours or the order auto-cancels and the buyer is refunded.</p>
            </a>
        @endif

        <div class="mt-6 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <h2 class="px-5 py-4 text-sm font-black uppercase tracking-wide text-gray-500 border-b border-gray-100">Recent orders</h2>

            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-400">
                    <tr><th class="px-5 py-3">Order</th><th class="px-5 py-3">Buyer</th><th class="px-5 py-3">Product</th><th class="px-5 py-3">Earning</th><th class="px-5 py-3">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($recentItems as $item)
                        <tr wire:key="ri-{{ $item->id }}">
                            <td class="px-5 py-3 font-medium">{{ $item->order?->order_number ?? '—' }}</td>
                            <td class="px-5 py-3">{{ $item->order?->user?->name ?? '—' }}</td>
                            <td class="px-5 py-3 line-clamp-1">{{ $item->product_name }} ×{{ $item->quantity }}</td>
                            <td class="px-5 py-3 font-bold">{{ money($item->seller_earning) }}</td>
                            <td class="px-5 py-3">{!! status_badge($item->status) !!}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">No orders yet — list products and share your store!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
