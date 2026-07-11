<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Behind Cloudflare + the host's proxy every generated URL must be https;
        // pairs with the locked trustProxies CIDRs in bootstrap/app.php.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
