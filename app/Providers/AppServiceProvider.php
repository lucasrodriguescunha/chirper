<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * RegisterController any application services.
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
        Paginator::defaultView('vendor.pagination.simple-tailwind');

        Event::listen(function (NotificationSent $event) {
            if ($event->notifiable instanceof User) {
                $event->notifiable->forgetUnreadNotificationsCount();
            }
        });

        if ($this->app->isProduction()) {
            URL::forceScheme('https');

            if (config('app.debug')) {
                throw new \RuntimeException('APP_DEBUG must be false in production to avoid leaking stack traces and environment data.');
            }
        }

        Password::defaults(function () {
            $rule = Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols();

            return $this->app->isProduction()
                ? $rule->uncompromised()
                : $rule;
        });
    }
}
