<?php

namespace App\Providers;

use App\Events\OrderPaid;
use App\Listeners\ProcessPaidOrder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Admins pass every authorization check.
        Gate::before(fn ($user, $ability) => $user->hasRole('admin') ? true : null);

        Event::listen(OrderPaid::class, ProcessPaidOrder::class);
    }
}
