<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Group;
use App\Observers\GroupObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Group::observe(GroupObserver::class);
    }
}
