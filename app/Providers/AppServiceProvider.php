<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Material;
use App\Observers\MaterialObserver;

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
        // Register Material Observer to protect against accidental soft deletion
        Material::observe(MaterialObserver::class);
    }
}
