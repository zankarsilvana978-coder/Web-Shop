<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <span class="text-xl font-black tracking-tight text-orange-600">SOUK<span class="text-gray-900">ELKOM</span></span>
                    </a>
                </div>

                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex sm:items-center">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home') || request()->routeIs('categories.show')">
                        {{ __('Shop') }}
                    </x-nav-link>

                    @auth
                        @unless(auth()->user()->isSeller() || auth()->user()->isAdmin())
                            @if (auth()->user()->seller && auth()->user()->seller->status === \App\Enums\SellerStatus::Pending)
                                <span class="text-sm text-amber-600 font-medium">{{ __('Store under review') }}</span>
                            @else
                                <x-nav-link :href="route('sell.apply')" :active="request()->routeIs('sell.apply')">
                                    {{ __('Become a Seller') }}
                                </x-nav-link>
                            @endif
                        @endunless

                        @if (auth()->user()->isSeller())
                            <x-nav-link :href="route('seller.dashboard')" :active="request()->routeIs('seller.*')">
                                {{ __('Seller Hub') }}
                            </x-nav-link>
                        @endif

                        @if (auth()->user()->isAdmin())
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                                {{ __('Admin') }}
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <a href="{{ route('cart') }}" class="relative mr-3 inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-orange-600" wire:navigate>
                        {{ __('Cart') }}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5 ml-1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
                    </a>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('orders.index')">{{ __('My Orders') }}</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-orange-600 px-3">{{ __('Log in') }}</a>
                    <a href="{{ route('register') }}" class="text-sm font-semibold bg-orange-600 text-white rounded-lg px-4 py-2 hover:bg-orange-500">{{ __('Sign up') }}</a>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                <x-responsive-nav-link :href="route('cart')" :active="request()->routeIs('cart')">{{ __('Cart') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">{{ __('My Orders') }}</x-responsive-nav-link>

                @unless(auth()->user()->isSeller() || auth()->user()->isAdmin())
                    @unless(auth()->user()->seller?->status === \App\Enums\SellerStatus::Pending)
                        <x-responsive-nav-link :href="route('sell.apply')" :active="request()->routeIs('sell.apply')">{{ __('Become a Seller') }}</x-responsive-nav-link>
                    @endunless
                @endunless

                @if (auth()->user()->isSeller())
                    <x-responsive-nav-link :href="route('seller.dashboard')">{{ __('Seller Hub') }}</x-responsive-nav-link>
                @endif

                @if (auth()->user()->isAdmin())
                    <x-responsive-nav-link :href="route('admin.dashboard')">{{ __('Admin Panel') }}</x-responsive-nav-link>
                @endif
            @else
                <x-responsive-nav-link :href="route('login')">{{ __('Log in') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('register')">{{ __('Sign up') }}</x-responsive-nav-link>
            @endauth
        </div>

        @auth
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</nav>
