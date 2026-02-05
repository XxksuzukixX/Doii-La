<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

use Laravel\Fortify\Fortify;

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
        //
        View::composer('*', function ($view) {
            $theme = Auth::check()
                ? Auth::user()->theme
                : 'indigo';

            $view->with('theme', $theme);
        });

        Fortify::verifyEmailView(function () {
        return view('auth.verify-email');
    });
    }
    
    
}
