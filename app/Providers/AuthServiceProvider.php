<?php

namespace App\Providers;

use App\Models\Board;
use App\Models\Task;
use App\Policies\BoardPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    protected $policies = [
        Task::class => TaskPolicy::class,
        Board::class => BoardPolicy::class,
    ];


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
