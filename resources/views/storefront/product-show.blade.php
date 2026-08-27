<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="grid lg:grid-cols-2 gap-8">
        <div>
            <div class="aspect-square bg-gradient-to-br from-orange-100 via-amber-50 to-gray-100 rounded-2xl relative overflow-hidden">
                @if ($product->getMedia('images')->isNotEmpty())
                    <img src="{{ $product->getFirstMediaUrl('images') }}" alt="{{ $product->name }}" class="absolute inset-0 w-full h-full object-cover" />
                @else
                    <div class="absolute inset-0 flex items-center justify-center text-orange-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-20 h-20"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M18 6h.008v.008H18V6Zm2.25 12H3.75A1.5 1.5 0 0 1 2.25 16.5v-9A1.5 1.5 0 0 1 3.75 6h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5Z" /></svg>
                    </div>
                @endif
            </div>

            @if ($product->getMedia('images')->count() > 1)
                <div class="mt-3 grid grid-cols-5 gap-2">
                    @foreach ($product->getMedia('images') as $media)
                        <img src="{{ $media->getUrl() }}" alt="" class="rounded-lg border border-gray-200 aspect-square object-cover" />
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            @if ($product->category)
                <p class="text-xs font-bold uppercase tracking-wide text-orange-600">{{ $product->category->name }}</p>
            @endif
            <h1 class="mt-1 text-2xl sm:text-3xl font-black text-gray-900">{{ $product->name }}</h1>

            <div class="mt-3 flex items-baseline gap-3">
                <span class="text-3xl font-black text-gray-900">{{ money($product->price) }}</span>
                @if ($product->stock > 0 && $product->stock <= 5)
                    <span class="text-sm font-semibold text-red-600">Only {{ $product->stock }} left in stock</span>
                @endif
            </div>

            <p class="mt-4 text-gray-600 leading-relaxed whitespace-pre-line">{{ $product->description }}</p>

            <dl class="mt-6 space-y-2 text-sm border-t border-gray-100 pt-4">
                <div class="flex gap-2"><dt class="font-semibold text-gray-500 w-28">Sold by</dt><dd>{{ $product->seller->store_name }}</dd></div>
                <div class="flex gap-2"><dt class="font-semibold text-gray-500 w-28">SKU</dt><dd>{{ $product->sku ?? '—' }}</dd></div>
                <div class="flex gap-2"><dt class="font-semibold text-gray-500 w-28">Shipping</dt><dd>Flat {{ money(\App\Models\Setting::get('shipping_flat_rate')) }} per order — shipped directly by the seller</dd></div>
            </dl>

            @if ($product->stock > 0)
                @livewire('storefront.add-to-cart-control', ['product' => $product], key($product->id))
            @else
                <p class="mt-6 inline-block rounded-lg bg-gray-100 px-6 py-3 text-sm font-bold text-gray-500">Out of stock</p>
            @endif
        </div>
    </div>
</div>
