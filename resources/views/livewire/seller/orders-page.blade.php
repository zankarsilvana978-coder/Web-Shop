<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-black text-gray-900">My Orders</h1>
            <p class="text-sm text-gray-500">Ship within 48 hours of payment to keep your rating high.</p>
        </div>

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

    @if ($showShipForm)
        <form wire:submit="markShipped" class="mt-6 bg-white rounded-xl border-2 border-orange-200 p-6 max-w-lg space-y-4">
            <h2 class="font-black">Mark as shipped</h2>

            <div>
                <label class="block text-sm font-medium mb-1">Tracking number *</label>
                <input type="text" wire:model="tracking_number" placeholder="e.g. ARX123456" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                @error('tracking_number') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Carrier (optional)</label>
                <input type="text" wire:model="carrier" placeholder="e.g. Aramex, LibanPost" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                @error('carrier') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-lg bg-orange-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-orange-500">Confirm shipment</button>
                <button type="button" wire:click="$set('showShipForm', false)" class="rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-200">Cancel</button>
            </div>
        </form>
    @endif

    <div class="mt-6 space-y-3">
        @forelse ($items as $item)
            <div class="bg-white rounded-xl border border-gray-200 p-5 flex flex-wrap items-start justify-between gap-3" wire:key="soi-{{ $item->id }}">
                <div>
                    <p class="font-bold text-gray-900">{{ $item->order?->order_number }}</p>
                    <p class="text-sm mt-1">{{ $item->product_name }} ×{{ $item->quantity }} — buyer: {{ $item->order?->user?->name }}</p>

                    @if ($item->order?->paid_at)
                        <p class="text-xs text-gray-400 mt-0.5">Paid {{ $item->order->paid_at->diffForHumans() }}</p>
                    @endif

                    @if ($item->tracking_number)
                        <p class="text-xs text-blue-700 font-medium mt-1">Tracking: {{ $item->tracking_number }}{{ $item->carrier ? ' · '.$item->carrier : '' }}</p>
                    @endif

                    @if ($item->cancellation_reason)
                        <p class="text-xs text-red-600 mt-1">{{ $item->cancellation_reason }}</p>
                    @endif

                    <div class="mt-2">{!! status_badge($item->status) !!}</div>
                </div>

                <div class="flex flex-col items-end gap-2">
                    <span class="text-sm text-gray-500">Earning</span>
                    <span class="font-black text-emerald-600">{{ money($item->seller_earning) }}</span>

                    @if ($item->status === \App\Enums\OrderItemStatus::AwaitingShipment)
                        <button wire:click="openShipForm({{ $item->id }})" class="rounded-lg bg-orange-600 px-4 py-2 text-xs font-bold text-white hover:bg-orange-500">
                            Mark as shipped
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400">No orders here yet.</div>
        @endforelse
    </div>

    {{ $items->links() }}
</div>
