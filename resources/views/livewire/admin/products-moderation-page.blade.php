<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-black text-gray-900">Manage Products</h1>
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
    @if (session('error'))
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm font-medium">{{ session('error') }}</div>
    @endif

    <div class="mt-6 space-y-4">
        @forelse ($products as $product)
            <div class="bg-white rounded-xl border border-gray-200 p-5" wire:key="mp-{{ $product->id }}">
                <div class="flex flex-wrap gap-4">
                    <div class="flex gap-3 flex-1 min-w-[240px]">
                        <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-orange-100 to-amber-50 overflow-hidden shrink-0">
                            @if ($product->getFirstMediaUrl('images'))<img src="{{ $product->getFirstMediaUrl('images') }}" class="w-full h-full object-cover" />@endif
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold line-clamp-1">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ money($product->price) }} · stock {{ $product->stock }} · by <span class="font-semibold">{{ $product->seller?->store_name }}</span> · {{ $product->category?->name ?? '—' }}
                            </p>
                            <p class="mt-1.5">{!! status_badge($product->status) !!}</p>
                            @if ($product->rejection_reason)
                                <p class="text-xs text-red-500 mt-1">Reason: {{ $product->rejection_reason }}</p>
                            @endif
                        </div>
                    </div>

                    @if ($product->status === \App\Enums\ProductStatus::PendingReview)
                        <div class="flex flex-col items-end gap-2 justify-center">
                            <button wire:click="approve({{ $product->id }})" wire:confirm="Approve and publish this product?"
                                    class="rounded-lg bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-emerald-500">Approve &amp; Publish</button>
                            <button
                                    onclick="const r = prompt('Why are you rejecting this product?', 'Missing or unclear product image.'); if (r) $wire.reject({{ $product->id }}, r);"
                                    class="rounded-lg bg-red-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-red-500">Reject…</button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400">Nothing here.</div>
        @endforelse
    </div>

    {{ $products->links() }}
</div>
