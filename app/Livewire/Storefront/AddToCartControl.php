<?php

namespace App\Livewire\Storefront;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;

class AddToCartControl extends Component
{
    public Product $product;

    public int $quantity = 1;

    public bool $added = false;

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function addToCart(CartService $cart): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        $this->validate(['quantity' => 'required|integer|min:1|max:99']);

        $cart->add(auth()->user(), $this->product, $this->quantity);

        $this->added = true;
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.storefront.add-to-cart-control');
    }
}
