<?php

namespace App\Livewire\Cart;

use App\Services\CartService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app')]
class CartPage extends Component
{
    #[On('cart-updated')]
    public function refreshCart(): void {}

    public function updateQuantity(int $itemId, int $quantity, CartService $cart): void
    {
        try {
            $cart->updateQuantity(auth()->user(), $itemId, $quantity);
        } catch (ValidationException $e) {
            $this->addError('cart', $e->errors()['quantity'][0] ?? 'Invalid quantity.');
        }
    }

    public function removeItem(int $itemId, CartService $cart): void
    {
        $cart->remove(auth()->user(), $itemId);
        $this->dispatch('cart-updated');
    }

    public function render(CartService $cart)
    {
        return view('livewire.cart.cart-page', [
            'items' => $cart->itemsFor(auth()->user()),
            'totals' => $cart->totals(auth()->user()),
        ]);
    }
}
