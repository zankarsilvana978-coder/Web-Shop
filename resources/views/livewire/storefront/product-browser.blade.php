<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-gradient-to-r from-orange-500 to-amber-500 rounded-2xl px-6 py-10 sm:py-14 text-center mb-8">
        <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight">The Local Marketplace Where Everyone Wins</h1>
        <p class="mt-3 text-orange-100 text-sm sm:text-lg">Shop from Lebanon's best local sellers. One cart, one payment, delivered by the sellers themselves.</p>
    </div>

    <div class="grid lg:grid-cols-[240px_1fr] gap-6">
        <aside class="space-y-4">
            <div>
                <label for="search" class="sr-only">Search</label>
                <input id="search" type="search" placeholder="Search products..." wire:model.live.debounce.300ms="search"
                       class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500 shadow-sm" />
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-2">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Categories</p>
                <button wire:click="$set('category', null)"
                        class="block w-full text-left px-3 py-2 rounded-lg text-sm {{ $category === null ? 'bg-orange-100 text-orange-700 font-semibold' : 'hover:bg-gray-50 text-gray-700' }}">
                    All categories
                </button>
                @foreach ($categories as $cat)
                    <button wire:click="$set('category', {{ $cat->id }})"
                            class="block w-full text-left px-3 py-2 rounded-lg text-sm {{ $category === $cat->id ? 'bg-orange-100 text-orange-700 font-semibold' : 'hover:bg-gray-50 text-gray-700' }}">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Price range ($)</p>
                <div class="flex items-center gap-2">
                    <input type="number" min="0" step="0.01" placeholder="Min" wire:model.debounce.400ms="minPrice"
                           class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500 text-sm" />
                    <span class="text-gray-400">—</span>
                    <input type="number" min="0" step="0.01" placeholder="Max" wire:model.debounce.400ms="maxPrice"
                           class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500 text-sm" />
                </div>

                <p class="text-xs font-bold uppercase tracking-wide text-gray-400 pt-1">Sort by</p>
                <select wire:model.live="sort" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500 text-sm">
                    <option value="newest">Newest</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                </select>

                <button wire:click="resetFilters" class="text-xs text-orange-600 hover:underline font-medium">Reset filters</button>
            </div>
        </aside>

        <section>
            @if (session('success') && !auth()->check())
            @endif

            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-gray-500">{{ $products->total() }} product(s) found</p>
            </div>

            <div wire:key="grid" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                @forelse ($products as $product)
                    <article class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition group flex flex-col">
                        <a href="{{ route('products.show', $product) }}" wire:navigate>
                            <div class="aspect-square bg-gradient-to-br from-orange-100 via-amber-50 to-gray-100 relative overflow-hidden">
                                @if ($product->getFirstMediaUrl('images'))
                                    <img src="{{ $product->getFirstMediaUrl('images') }}" alt="{{ $product->name }}"
                                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy" />
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 text-orange-300"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M18 6h.008v.008H18V6Zm2.25 12H3.75A1.5 1.5 0 0 1 2.25 16.5v-9A1.5 1.5 0 0 1 3.75 6h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5Z" /></svg>
                                    </div>
                                @endif
                            </div>
                        </a>

                        <div class="p-3 flex flex-col flex-1">
                            <p class="text-[11px] uppercase tracking-wide text-gray-400">{{ $product->category?->name }}</p>
                            <a href="{{ route('products.show', $product) }}" wire:navigate class="mt-0.5 font-semibold text-sm text-gray-900 line-clamp-2 hover:text-orange-600">{{ $product->name }}</a>
                            <p class="mt-1 text-xs text-gray-500">by <span class="font-medium">{{ $product->seller->store_name }}</span></p>

                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-lg font-black text-gray-900">{{ money($product->price) }}</span>
                                @if ($product->stock <= 5)
                                    <span class="text-[11px] font-semibold text-red-600">Only {{ $product->stock }} left</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full text-center py-20">
                        <p class="text-gray-400 text-lg font-medium">No products found.</p>
                        <p class="text-gray-400 text-sm mt-1">Try adjusting your search or filters.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">{{ $products->links() }}</div>
        </section>
    </div>
</div>
