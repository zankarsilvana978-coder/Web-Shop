<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-2xl font-black text-gray-900">Become a Seller on Soukelkom</h1>
    <p class="mt-2 text-gray-600 text-sm">Open your store, list your products and ship directly to buyers across Lebanon. The platform takes a small commission only when you sell.</p>

    @if ($submitted)
        @php
            $seller = \App\Models\Seller::query()->where('user_id', auth()->id())->first();
        @endphp

        <div class="mt-8 bg-white rounded-xl border border-gray-200 p-8 text-center">
            <div class="mx-auto w-14 h-14 rounded-full bg-orange-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-orange-600"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" /></svg>
            </div>

            <p class="mt-4 font-bold text-lg text-gray-900">{{ $seller->store_name }}</p>
            <p class="mt-1 text-sm text-gray-500">
                @if ($seller->status === \App\Enums\SellerStatus::Pending)
                    Your application is under review. We will email you the decision — usually within 24 hours.
                @elseif ($seller->status === \App\Enums\SellerStatus::Approved)
                    Approved! Visit your <a href="{{ route('seller.dashboard') }}" wire:navigate class="text-orange-600 font-semibold hover:underline">Seller Hub</a>.
                @elseif ($seller->status === \App\Enums\SellerStatus::Rejected)
                    Application rejected{{ $seller->rejection_reason ? ' — '.$seller->rejection_reason : '' }}.
                @endif
            </p>
        </div>
    @else
        <form wire:submit="submit" class="mt-8 bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Store name</label>
                <input type="text" wire:model="store_name" placeholder="e.g. Ahmed Electronics" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                @error('store_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact phone</label>
                <input type="text" wire:model="phone" placeholder="+961 3 ..." class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                @error('phone') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tell us about your store</label>
                <textarea wire:model="description" rows="4" placeholder="What do you sell? How do you ship?" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"></textarea>
                @error('description') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" wire:loading.attr="disabled"
                    class="w-full rounded-lg bg-orange-600 px-6 py-3 text-sm font-bold text-white hover:bg-orange-500 disabled:opacity-50">
                Apply to become a seller
            </button>
        </form>
    @endif
</div>
