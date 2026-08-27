<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-black text-gray-900">Store Settings</h1>

    @if (session('success'))
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <form wire:submit="save" class="mt-6 bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Store name</label>
            <input type="text" wire:model="store_name" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
            @error('store_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Contact phone</label>
            <input type="text" wire:model="phone" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
            @error('phone') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Store description</label>
            <textarea wire:model="description" rows="4" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"></textarea>
            @error('description') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="rounded-lg bg-gray-50 p-4 text-sm">
            <p class="text-gray-500">Your commission rate: <strong class="text-gray-900">{{ number_format((float) $commissionRate, 2) }}%</strong></p>
            <p class="text-xs text-gray-400 mt-0.5">Set by the platform admin.</p>
        </div>

        <button type="submit" class="rounded-lg bg-orange-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-orange-500">Save settings</button>
    </form>
</div>
