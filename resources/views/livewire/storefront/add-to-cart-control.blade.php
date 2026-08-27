<div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
        <button type="button" wire:click="$set('quantity', quantity - 1)" class="px-3 py-2 text-gray-600 hover:bg-gray-100 {{ $product->stock < 2 ? 'opacity-40' : '' }}" @if($quantity <= 1) disabled @endif>&minus;</button>
        <span class="w-12 text-center font-semibold">{{ $quantity }}</span>
        <button type="button" wire:click="$set('quantity', quantity + 1)" class="px-3 py-2 text-gray-600 hover:bg-gray-100" @if($quantity >= $product->stock) disabled @endif>+</button>
    </div>

    <button wire:click="addToCart"
            class="flex-1 inline-flex justify-center items-center gap-2 rounded-lg bg-orange-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-400">
        @if ($added)
            ✓ Added to cart
        @else
            Add to Cart — {{ money($product->price * $quantity) }}
        @endif
    </button>
</div>

@error('quantity') <p class="text-red-600 text-xs mt-2">{{ $message }}</p> @enderror
