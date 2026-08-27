<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        protected CommissionService $commissions,
    ) {}

    /**
     * One cart -> ONE order, many order items (one per product/seller).
     * The buyer pays once; the money split is frozen per item here.
     */
    public function placeOrder(User $buyer, array $data): Order
    {
        return DB::transaction(function () use ($buyer, $data) {
            $cart = Cart::query()
                ->where('user_id', $buyer->id)
                ->with(['items.product.seller'])
                ->lockForUpdate()
                ->first();

            if (! $cart || $cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
            }

            foreach ($cart->items as $item) {
                $product = $item->product;

                if (! $product || $product->status !== ProductStatus::Active) {
                    throw ValidationException::withMessages([
                        'cart' => "'{$item->product?->name}' is no longer available.",
                    ]);
                }

                if ($product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Only {$product->stock} unit(s) of '{$product->name}' left in stock.",
                    ]);
                }
            }

            $subtotal = round(
                $cart->items->sum(fn ($item) => $item->quantity * (float) $item->product->price),
                2,
            );
            $shippingFee = (float) Setting::get('shipping_flat_rate');
            $total = round($subtotal + $shippingFee, 2);

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $buyer->id,
                'status' => OrderStatus::PendingPayment,
                'payment_method' => PaymentMethod::from($data['payment_method']),
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $total,
                'shipping_name' => $data['shipping_name'],
                'shipping_phone' => $data['shipping_phone'],
                'shipping_city' => $data['shipping_city'],
                'shipping_address' => $data['shipping_address'],
            ]);

            foreach ($cart->items as $item) {
                $product = $item->product;

                // Atomic guarded decrement: never oversell under concurrency.
                $decremented = Product::query()
                    ->whereKey($product->id)
                    ->where('stock', '>=', $item->quantity)
                    ->decrement('stock', $item->quantity);

                if ($decremented === 0) {
                    throw ValidationException::withMessages([
                        'cart' => "'{$product->name}' just sold out. Please adjust your cart.",
                    ]);
                }

                $lineSubtotal = round($item->quantity * (float) $product->price, 2);
                $split = $this->commissions->splitFor($product, $lineSubtotal);

                $order->items()->create([
                    'seller_id' => $product->seller_id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $product->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $lineSubtotal,
                    'commission_rate' => sprintf('%.2f', $this->commissions->rateFor($product)),
                    'commission_amount' => sprintf('%.2f', $split['commission']),
                    'seller_earning' => sprintf('%.2f', $split['earning']),
                ]);
            }

            $cart->items()->delete();

            return $order;
        });
    }
}
