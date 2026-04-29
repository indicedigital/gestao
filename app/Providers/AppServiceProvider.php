<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

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
        // Layout usa Bootstrap 5; o padrão do Laravel é Tailwind (classes quebram sem Tailwind).
        Paginator::useBootstrapFive();
    }
}
