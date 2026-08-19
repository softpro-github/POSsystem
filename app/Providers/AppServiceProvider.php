<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
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
        Event::listen(Login::class, function (Login $event) {
            activity()->causedBy($event->user)->log('login');
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                activity()->causedBy($event->user)->log('logout');
            }
        });
    }
}
