<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-black text-gray-900">Site Settings</h1>
    <p class="text-sm text-gray-500">Global platform configuration.</p>

    @if (session('success'))
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <form wire:submit="save" class="mt-6 bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Site name</label>
                <input type="text" wire:model="site_name" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                @error('site_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Support email</label>
                <input type="email" wire:model="support_email" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                @error('support_email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Global commission rate (%)</label>
                <input type="number" step="0.01" min="0" max="100" wire:model="global_commission_rate"
                       class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                @error('global_commission_rate') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Shipping flat rate per order ($)</label>
                <input type="number" step="0.01" min="0" wire:model="shipping_flat_rate"
                       class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                @error('shipping_flat_rate') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Minimum payout ($)</label>
                <input type="number" step="0.01" min="0" wire:model="payout_min"
                       class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                @error('payout_min') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Ship deadline (hours)</label>
                <input type="number" min="1" max="720" wire:model="ship_deadline_hours"
                       class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                @error('ship_deadline_hours') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Earning hold after delivery (days)</label>
                <input type="number" min="0" max="365" wire:model="earning_hold_days"
                       class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                @error('earning_hold_days') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <button type="submit" wire:loading.attr="disabled"
                class="rounded-lg bg-orange-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-orange-500 disabled:opacity-50">
            Save settings
        </button>
    </form>

    <p class="mt-3 text-xs text-gray-400">Per-seller commission overrides are managed on the Sellers page and take precedence over the global rate.</p>
</div>
