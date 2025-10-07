<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Product;
use App\Models\Order;
use App\Models\Review;
use App\Policies\ProductPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ReviewPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Order::class => OrderPolicy::class,
        Review::class => ReviewPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define admin gate
        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });

        // Define seller gate
        Gate::define('seller', function ($user) {
            return $user->role === 'seller';
        });

        // Define buyer gate
        Gate::define('buyer', function ($user) {
            return $user->role === 'buyer' || $user->role === 'customer';
        });
    }
}
