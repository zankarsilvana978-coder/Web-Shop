<?php

namespace App\Livewire\Checkout;

use App\Enums\PaymentMethod;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CheckoutPage extends Component
{
    public string $shipping_name = '';

    public string $shipping_phone = '';

    public string $shipping_city = '';

    public string $shipping_address = '';

    public string $payment_method = 'manual_transfer';

    public function mount(): void
    {
        $user = auth()->user();
        $this->shipping_name = $user->name;
    }

    protected function rules(): array
    {
        return [
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:30',
            'shipping_city' => 'required|string|max:100',
            'shipping_address' => 'required|string|max:500',
            'payment_method' => 'required|in:manual_transfer'.(PaymentService::stripeEnabled() ? ',stripe' : ''),
        ];
    }

    public function placeOrder(CartService $carts, CheckoutService $checkout)
    {
        $this->validate();

        try {
            $order = $checkout->placeOrder(auth()->user(), $this->only([
                'shipping_name', 'shipping_phone', 'shipping_city', 'shipping_address', 'payment_method',
            ]));
        } catch (ValidationException $e) {
            foreach ($e->errors() as $messages) {
                $this->addError('cart', $messages[0]);
            }

            return;
        }

        session()->flash('success', "Order {$order->order_number} placed! Complete the payment to proceed.");

        return $this->redirectRoute('orders.show', $order, navigate: true);
    }

    public function render(CartService $cart)
    {
        return view('livewire.checkout.checkout-page', [
            'items' => $cart->itemsFor(auth()->user()),
            'totals' => $cart->totals(auth()->user()),
            'stripeEnabled' => PaymentService::stripeEnabled(),
            'methods' => PaymentMethod::cases(),
        ]);
    }
}
