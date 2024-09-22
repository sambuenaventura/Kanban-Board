<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
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
        // Register a custom path for component
        Blade::component('App\View\Components\BoardModal', 'board-modal');
        Blade::component('App\View\Components\TaskModal', 'task-modal');
    }
}
