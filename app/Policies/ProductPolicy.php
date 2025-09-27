<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Anyone can view products
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        // Approved products can be viewed by anyone
        // Pending/rejected products can only be viewed by the seller or admin
        if ($product->status === 'approved') {
            return true;
        }

        return $user->id === $product->seller_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only sellers and admins can create products
        return $user->hasRole('seller') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        // Only the seller who owns the product or admin can update it
        return $user->id === $product->seller_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        // Only the seller who owns the product or admin can delete it
        return $user->id === $product->seller_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can approve the product.
     */
    public function approve(User $user, Product $product): bool
    {
        // Only admins can approve products
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can reject the product.
     */
    public function reject(User $user, Product $product): bool
    {
        // Only admins can reject products
        return $user->hasRole('admin');
    }
}
