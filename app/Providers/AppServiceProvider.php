<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        RedirectIfAuthenticated::redirectUsing(function ($request) {
            $redirectTo = $request->user()->hasRole('admin') ? 'admin.dashboard' : 'user.dashboard';
            return route($redirectTo);
        });

        Vite::prefetch(concurrency: 3);
    }
}
