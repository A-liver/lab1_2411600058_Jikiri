<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        // Admin-only actions (transaction delete, stock adjustments)
        Gate::define('admin', fn (User $user) => $user->isAdmin());
        View::composer('layouts.app', function ($view) {
        $user = auth()->user();

        $view->with([
            'unreadNotificationsCount' => $user ? $user->unreadNotifications()->count() : 0,
            'recentNotifications' => $user ? $user->notifications()->latest()->take(5)->get() : collect(),
        ]);
    });
    }
}