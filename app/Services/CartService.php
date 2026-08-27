<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    /** Get or lazily create the cart for a user. */
    public function cartFor(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    public function itemsFor(User $user)
    {
        return $this->cartFor($user)->items()->with(['product.seller', 'product.category'])->get();
    }

    public function add(User $user, Product $product, int $quantity = 1): void
    {
        if ($product->status !== ProductStatus::Active) {
            throw ValidationException::withMessages(['product' => 'This product is not available.']);
        }

        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'Invalid quantity.']);
        }

        DB::transaction(function () use ($user, $product, $quantity) {
            $cart = $this->cartFor($user);

            $item = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            $newQuantity = ($item?->quantity ?? 0) + $quantity;

            if ($newQuantity > $product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$product->stock} unit(s) of '{$product->name}' in stock.",
                ]);
            }

            if ($item) {
                $item->increment('quantity', $quantity);
            } else {
                CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => $quantity]);
            }
        });
    }

    public function updateQuantity(User $user, int $itemId, int $quantity): void
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'Quantity must be at least 1.']);
        }

        DB::transaction(function () use ($user, $itemId, $quantity) {
            $item = CartItem::query()
                ->whereHas('cart', fn ($q) => $q->where('user_id', $user->id))
                ->lockForUpdate()
                ->findOrFail($itemId);

            if ($quantity > $item->product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$item->product->stock} unit(s) available.",
                ]);
            }

            $item->update(['quantity' => $quantity]);
        });
    }

    public function remove(User $user, int $itemId): void
    {
        CartItem::query()
            ->whereHas('cart', fn ($q) => $q->where('user_id', $user->id))
            ->whereKey($itemId)
            ->delete();
    }

    /** @return array{subtotal: float, shipping_fee: float, total: float} */
    public function totals(User $user): array
    {
        $subtotal = round(
            $this->itemsFor($user)->sum(fn ($item) => $item->quantity * (float) $item->product->price),
            2,
        );

        $shippingFee = $subtotal > 0 ? (float) Setting::get('shipping_flat_rate') : 0.0;

        return [
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'total' => round($subtotal + $shippingFee, 2),
        ];
    }
}
