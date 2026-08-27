<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-black text-gray-900">Shopping Cart</h1>

    @error('cart')
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">{{ $message }}</div>
    @enderror

    <div class="mt-6 grid lg:grid-cols-[1fr_320px] gap-6 items-start">
        <div class="space-y-3">
            @forelse ($items as $item)
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex gap-4" wire:key="item-{{ $item->id }}">
                    <div class="w-20 h-20 rounded-lg bg-gradient-to-br from-orange-100 to-amber-50 overflow-hidden shrink-0">
                        @if ($item->product->getFirstMediaUrl('images'))
                            <img src="{{ $item->product->getFirstMediaUrl('images') }}" class="w-full h-full object-cover" alt="" />
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <a href="{{ route('products.show', $item->product) }}" wire:navigate class="font-semibold text-gray-900 hover:text-orange-600 line-clamp-1">{{ $item->product->name }}</a>
                        <p class="text-xs text-gray-500 mt-0.5">Sold by {{ $item->product->seller->store_name }} · {{ money($item->product->price) }} each</p>

                        <div class="mt-2 flex items-center justify-between">
                            <div class="inline-flex items-center border border-gray-300 rounded-lg">
                                <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})" class="px-2.5 py-1 text-gray-600 hover:bg-gray-100" @if($item->quantity <= 1) disabled @endif>&minus;</button>
                                <span class="w-10 text-center text-sm font-semibold">{{ $item->quantity }}</span>
                                <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})" class="px-2.5 py-1 text-gray-600 hover:bg-gray-100">+</button>
                            </div>
                            <span class="font-bold">{{ money($item->product->price * $item->quantity) }}</span>
                        </div>
                    </div>

                    <button wire:click="removeItem({{ $item->id }})" wire:confirm="Remove this item?" class="self-start text-gray-400 hover:text-red-600" title="Remove">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                    </button>
                </div>
            @empty
                <div class="text-center py-16 bg-white rounded-xl border border-gray-200">
                    <p class="text-gray-500 font-medium">Your cart is empty.</p>
                    <a href="{{ route('home') }}" wire:navigate class="mt-3 inline-block text-orange-600 font-semibold hover:underline">Start shopping</a>
                </div>
            @endforelse
        </div>

        @if ($items->isNotEmpty())
            <aside class="bg-white rounded-xl border border-gray-200 p-5 space-y-3 lg:sticky lg:top-6">
                <h2 class="font-black text-gray-900">Order Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd class="font-medium">{{ money($totals['subtotal']) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Shipping (flat rate)</dt><dd class="font-medium">{{ money($totals['shipping_fee']) }}</dd></div>
                    <div class="border-t border-gray-100 pt-2 flex justify-between text-base"><dt class="font-bold">Total</dt><dd class="font-black text-orange-600">{{ money($totals['total']) }}</dd></div>
                </dl>

                <a href="{{ route('checkout') }}" wire:navigate
                   class="block text-center rounded-lg bg-orange-600 px-6 py-3 text-sm font-bold text-white hover:bg-orange-500">
                    Proceed to Checkout
                </a>
            </aside>
        @endif
    </div>
</div>
