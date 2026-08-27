<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-black text-gray-900">My Products</h1>
            <p class="text-sm text-gray-500">Create, edit and submit products for review.</p>
        </div>

        <button wire:click="createNew" class="rounded-lg bg-orange-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-orange-500">+ New Product</button>
    </div>

    @if (session('success'))
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    @if ($showForm)
        <form wire:submit="save(false)" class="mt-6 bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-black">{{ $editingId ? 'Edit product' : 'New product' }}</h2>
                <button type="button" wire:click="$set('showForm', false)" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input type="text" wire:model="name" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                    @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Category</label>
                    <select wire:model="category_id" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                        <option value="">— choose —</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Price ($)</label>
                    <input type="number" step="0.01" min="0" wire:model="price" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                    @error('price') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Stock</label>
                    <input type="number" min="0" wire:model="stock" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                    @error('stock') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">SKU (optional)</label>
                    <input type="text" wire:model="sku" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                    @error('sku') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Images (up to 5, max 4MB each)</label>
                    <input type="file" wire:model="images" multiple accept="image/png,image/jpeg,image/webp"
                           class="w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-orange-50 file:px-3 file:py-2 file:text-orange-700" />
                    @error('images.*') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea wire:model="description" rows="4" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"></textarea>
                @error('description') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-gray-800 px-5 py-2.5 text-sm font-bold text-white hover:bg-gray-700 disabled:opacity-50">
                    Save as draft
                </button>
                <button type="button" wire:loading.attr="disabled" wire:click="save(true)"
                        class="rounded-lg bg-orange-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-orange-500 disabled:opacity-50">
                    Submit for review
                </button>
                <span wire:loading wire:target="images"><span class="text-sm text-gray-500 self-center">Uploading images…</span></span>
            </div>
        </form>
    @endif

    <div class="mt-6 bg-white rounded-xl border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm min-w-[720px]">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-400">
                <tr><th class="px-5 py-3">Product</th><th class="px-5 py-3">Category</th><th class="px-5 py-3">Price</th><th class="px-5 py-3">Stock</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($products as $product)
                    <tr wire:key="p-{{ $product->id }}" class="hover:bg-gray-25">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-100 to-amber-50 overflow-hidden shrink-0">
                                    @if ($product->getFirstMediaUrl('images'))<img src="{{ $product->getFirstMediaUrl('images') }}" class="w-full h-full object-cover" />@endif
                                </div>
                                <div>
                                    <p class="font-semibold line-clamp-1">{{ $product->name }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $product->sku ?? 'no SKU' }}</p>
                                </div>
                            </div>
                            @if ($product->status === \App\Enums\ProductStatus::Rejected && $product->rejection_reason)
                                <p class="mt-1 text-xs text-red-600 pl-[52px]">Reason: {{ $product->rejection_reason }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3">{{ $product->category?->name ?? '—' }}</td>
                        <td class="px-5 py-3 font-bold">{{ money($product->price) }}</td>
                        <td class="px-5 py-3">{{ $product->stock }}</td>
                        <td class="px-5 py-3">{!! status_badge($product->status) !!}</td>
                        <td class="px-5 py-3 text-right space-x-2 whitespace-nowrap">
                            <button wire:click="edit({{ $product->id }})" class="text-orange-600 font-semibold hover:underline">Edit</button>
                            <button wire:click="delete({{ $product->id }})" wire:confirm="Delete this product?" class="text-red-500 font-semibold hover:underline">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No products yet. Create your first one!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $products->links() }}
</div>
