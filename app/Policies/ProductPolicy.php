<?php

namespace App\Policies;

use App\Enums\SellerStatus;
use App\Models\Product;
use App\Models\User;

/**
 * Ownership rules for sellers. Admins bypass everything via Gate::before.
 */
class ProductPolicy
{
    public function create(User $user): bool
    {
        return $user->seller !== null;
    }

    public function update(User $user, Product $product): bool
    {
        return $this->owns($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->owns($user, $product);
    }

    /** Only approved stores can push products into the review queue. */
    public function submitForReview(User $user, Product $product): bool
    {
        return $this->owns($user, $product) && $user->seller->status === SellerStatus::Approved;
    }

    protected function owns(User $user, Product $product): bool
    {
        return $user->seller !== null && $user->seller->id === $product->seller_id;
    }
}
