<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-black text-gray-900">Checkout</h1>

    @error('cart')
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">{{ $message }}</div>
    @enderror

    @if ($items->isEmpty())
        <div class="mt-6 bg-white rounded-xl border border-gray-200 p-10 text-center">
            <p class="text-gray-500">Your cart is empty.</p>
            <a href="{{ route('home') }}" wire:navigate class="text-orange-600 font-semibold hover:underline">Continue shopping</a>
        </div>
    @else
        <form wire:submit="placeOrder" class="mt-6 grid lg:grid-cols-[1fr_340px] gap-6 items-start">
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <h2 class="font-black text-gray-900">Shipping details</h2>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
                        <input type="text" wire:model="shipping_name" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                        @error('shipping_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" wire:model="shipping_phone" placeholder="+961 3 ..." class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                        @error('shipping_phone') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" wire:model="shipping_city" placeholder="Beirut, Tripoli, Saida..." class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" />
                    @error('shipping_city') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea wire:model="shipping_address" rows="3" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"></textarea>
                    @error('shipping_address') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <h2 class="font-black text-gray-900 pt-2">Payment method</h2>

                <label class="flex items-start gap-3 border rounded-xl p-4 cursor-pointer {{ $payment_method === 'manual_transfer' ? 'border-orange-500 ring-1 ring-orange-400' : 'border-gray-200' }}">
                    <input type="radio" wire:model.live="payment_method" value="manual_transfer" class="mt-1 text-orange-600 focus:ring-orange-500" />
                    <span>
                        <span class="block font-semibold text-sm text-gray-900">Bank Transfer (Manual)</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Transfer to our bank account, upload the receipt and we verify it within hours.</span>
                    </span>
                </label>

                <label class="flex items-start gap-3 border rounded-xl p-4 {{ $stripeEnabled ? 'cursor-pointer ' : 'opacity-60 cursor-not-allowed ' }}{{ $payment_method === 'stripe' ? 'border-orange-500 ring-1 ring-orange-400' : 'border-gray-200' }}">
                    <input type="radio" wire:model.live="payment_method" value="stripe" @disabled(! $stripeEnabled) class="mt-1 text-orange-600 focus:ring-orange-500" />
                    <span>
                        <span class="block font-semibold text-sm text-gray-900">Card Payment (Stripe)</span>
                        <span class="block text-xs text-gray-500 mt-0.5">
                            @if ($stripeEnabled)
                                Pay securely by card. Money is split automatically.
                            @else
                                Not configured yet — add Stripe API keys to enable.
                            @endif
                        </span>
                    </span>
                </label>

                @error('payment_method') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
            </div>

            <aside class="bg-white rounded-xl border border-gray-200 p-5 space-y-3 lg:sticky lg:top-6">
                <h2 class="font-black text-gray-900">Your order</h2>

                <ul class="space-y-2 text-sm">
                    @foreach ($items as $item)
                        <li class="flex justify-between gap-2">
                            <span class="line-clamp-1 text-gray-600">{{ $item->quantity }}× {{ $item->product->name }}</span>
                            <span class="font-medium whitespace-nowrap">{{ money($item->product->price * $item->quantity) }}</span>
                        </li>
                    @endforeach
                </ul>

                <dl class="space-y-2 text-sm border-t border-gray-100 pt-3">
                    <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd>{{ money($totals['subtotal']) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Shipping</dt><dd>{{ money($totals['shipping_fee']) }}</dd></div>
                    <div class="border-t border-gray-100 pt-2 flex justify-between text-base"><dt class="font-bold">Total</dt><dd class="font-black text-orange-600">{{ money($totals['total']) }}</dd></div>
                </dl>

                <button type="submit" wire:loading.attr="disabled"
                        class="w-full rounded-lg bg-orange-600 px-6 py-3 text-sm font-bold text-white hover:bg-orange-500 disabled:opacity-50">
                    Place order — {{ money($totals['total']) }}
                </button>
            </aside>
        </form>
    @endif
</div>
