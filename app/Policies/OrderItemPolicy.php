<?php

namespace App\Policies;

use App\Models\OrderItem;
use App\Models\User;

class OrderItemPolicy
{
    /** Only the seller who owns the item may ship it. */
    public function ship(User $user, OrderItem $item): bool
    {
        return $user->seller !== null && $user->seller->id === $item->seller_id;
    }
}
