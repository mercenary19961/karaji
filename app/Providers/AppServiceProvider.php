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

        // TEMP (local tunnel testing via cloudflared): the tunnel serves HTTPS but
        // talks plain HTTP to `php artisan serve`, so generated asset/route URLs
        // come out http:// and are blocked as mixed content → blank page. Force
        // https when reached through an https tunnel. REMOVE after tunnel testing.
        if (! $this->app->environment('production') && request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}
