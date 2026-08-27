<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900">Admin Dashboard</h1>
                <p class="text-sm text-gray-500">Platform overview at a glance.</p>
            </div>

            <nav class="flex flex-wrap gap-1 bg-white rounded-lg border border-gray-200 p-1">
                @foreach ([
                    'dashboard' => 'Dashboard',
                    'sellers' => 'Sellers',
                    'products' => 'Products',
                    'orders' => 'Orders',
                    'payouts' => 'Payouts',
                    'settings' => 'Settings',
                ] as $route => $label)
                    <a href="{{ route("admin.$route") }}" wire:current.exact="admin.{{ $route }}"
                       class="rounded-md px-3 py-1.5 text-sm font-medium {{ request()->routeIs("admin.$route") ? 'bg-orange-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Total platform revenue</p>
                <p class="mt-2 text-2xl font-black text-emerald-600">{{ money($kpis['total_commission']) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Commission this month</p>
                <p class="mt-2 text-2xl font-black text-orange-600">{{ money($kpis['month_commission']) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Active sellers</p>
                <p class="mt-2 text-2xl font-black text-gray-900">{{ $kpis['active_sellers'] }}</p>
                <p class="text-[11px] text-amber-600 mt-1">{{ $kpis['pending_sellers'] }} pending approval</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Pending payouts</p>
                <p class="mt-2 text-2xl font-black text-purple-600">{{ money($kpis['pending_payouts_amount']) }}</p>
                <p class="text-[11px] text-gray-400 mt-1">{{ $kpis['pending_payouts_count'] }} request(s)</p>
            </div>
        </div>

        <div class="mt-4 grid sm:grid-cols-3 gap-4">
            <a href="{{ route('admin.sellers') }}" wire:navigate class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 hover:bg-amber-100 transition">
                <p class="font-bold text-amber-800">{{ $kpis['pending_sellers'] }} seller application(s)</p>
                <p class="text-sm text-amber-700 mt-0.5">Review and approve →</p>
            </a>
            <a href="{{ route('admin.products') }}" wire:navigate class="rounded-xl border border-blue-200 bg-blue-50 px-5 py-4 hover:bg-blue-100 transition">
                <p class="font-bold text-blue-800">{{ $kpis['pending_products'] }} product(s) awaiting review</p>
                <p class="text-sm text-blue-700 mt-0.5">Moderate now →</p>
            </a>
            <a href="{{ route('admin.payouts') }}" wire:navigate class="rounded-xl border border-purple-200 bg-purple-50 px-5 py-4 hover:bg-purple-100 transition">
                <p class="font-bold text-purple-800">{{ money($kpis['pending_payouts_amount']) }} to pay out</p>
                <p class="text-sm text-purple-700 mt-0.5">Process payouts →</p>
            </a>
        </div>

        <div class="mt-6 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <h2 class="px-5 py-4 text-sm font-black uppercase tracking-wide text-gray-500 border-b border-gray-100">Latest orders</h2>

            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-400">
                    <tr><th class="px-5 py-3">Order</th><th class="px-5 py-3">Buyer</th><th class="px-5 py-3">Payment</th><th class="px-5 py-3">Total</th><th class="px-5 py-3">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($recentOrders as $order)
                        <tr wire:key="ro-{{ $order->id }}">
                            <td class="px-5 py-3 font-medium">{{ $order->order_number }}</td>
                            <td class="px-5 py-3">{{ $order->user?->name }}</td>
                            <td class="px-5 py-3 capitalize text-xs">{{ str_replace('_', ' ', $order->payment_method->value) }}</td>
                            <td class="px-5 py-3 font-bold">{{ money($order->total) }}</td>
                            <td class="px-5 py-3">{!! status_badge($order->status) !!}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
