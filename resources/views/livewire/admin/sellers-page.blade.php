<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-black text-gray-900">Manage Sellers</h1>
        <select wire:model.live="status" class="rounded-lg border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500">
            <option value="">All statuses</option>
            @foreach (\App\Enums\SellerStatus::cases() as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>
    </div>

    @if (session('success'))
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="mt-6 bg-white rounded-xl border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm min-w-[860px]">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-400">
                <tr>
                    <th class="px-5 py-3">Store</th><th class="px-5 py-3">Owner</th><th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Commission override</th><th class="px-5 py-3">Balances</th><th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($sellers as $seller)
                    <tr wire:key="s-{{ $seller->id }}">
                        <td class="px-5 py-4">
                            <p class="font-semibold">{{ $seller->store_name }}</p>
                            <p class="text-xs text-gray-400 line-clamp-1 max-w-[240px]">{{ $seller->description }}</p>
                            @if ($seller->rejection_reason)
                                <p class="text-xs text-red-500 mt-0.5">Reason: {{ $seller->rejection_reason }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <p>{{ $seller->user?->name }}</p>
                            <p class="text-xs text-gray-400">{{ $seller->user?->email }}</p>
                        </td>
                        <td class="px-5 py-4">{!! status_badge($seller->status) !!}</td>
                        <td class="px-5 py-4">
                            <form wire:submit.prevent="saveCommission({{ $seller->id }})" class="flex items-center gap-2">
                                <input type="number" step="0.01" min="0" max="100"
                                       placeholder="{{ number_format($globalRate, 2) }}"
                                       wire:model="commissionRates.{{ $seller->id }}"
                                       class="w-24 rounded-lg border-gray-300 text-sm focus:border-orange-500 focus:ring-orange-500" />
                                <span class="text-xs text-gray-400">% · {{ filled($seller->commission_rate) ? 'override' : 'global' }}</span>
                                <button type="submit" class="text-orange-600 font-bold text-xs hover:underline">Save</button>
                            </form>
                        </td>
                        <td class="px-5 py-4">
                            <p><span class="text-emerald-600 font-bold">{{ money($seller->balance) }}</span> <span class="text-[11px] text-gray-400">available</span></p>
                            <p><span class="text-gray-500 font-medium">{{ money($seller->pending_balance) }}</span> <span class="text-[11px] text-gray-400">on hold</span></p>
                        </td>
                        <td class="px-5 py-4 text-right space-x-2 whitespace-nowrap">
                            @if ($seller->status === \App\Enums\SellerStatus::Pending)
                                <button wire:click="approve({{ $seller->id }})" wire:confirm="Approve this seller?"
                                        class="rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-emerald-500">Approve</button>
                                <button
                                        onclick="const r = prompt('Rejection reason:', 'Application did not meet our criteria.'); if (r) $wire.reject({{ $seller->id }}, r);"
                                        class="rounded-lg bg-red-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-red-500">Reject</button>
                            @elseif ($seller->status === \App\Enums\SellerStatus::Approved)
                                <button wire:click="suspend({{ $seller->id }})" wire:confirm="Suspend this seller? Their products will be hidden."
                                        class="rounded-lg bg-gray-200 px-3.5 py-2 text-xs font-bold text-gray-700 hover:bg-gray-300">Suspend</button>
                            @elseif ($seller->status === \App\Enums\SellerStatus::Suspended)
                                <button wire:click="reactivate({{ $seller->id }})"
                                        class="rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-emerald-500">Reactivate</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No sellers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $sellers->links() }}
</div>
