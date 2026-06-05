<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        \Carbon\CarbonImmutable::setLocale('de');
        \Carbon\Carbon::setLocale('de');

        // Hinter einem Reverse-Proxy mit HTTPS-Terminierung erzeugt Laravel
        // sonst http://-Asset-URLs, die der Browser als "mixed content" blockiert.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
