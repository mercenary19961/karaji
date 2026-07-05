<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pin the locale to Arabic at the start of EVERY web request. The bootstrap
 * default alone is not enough: App::setLocale() mutates process state, so in
 * worker-mode servers (FrankenPHP/Octane) — and across requests inside one
 * test — a locale set for one request leaks into the next. Admin routes
 * override to English afterwards via SetAdminLocale (route middleware runs
 * after this group middleware).
 *
 * 'ar' is deliberate, not config('app.locale'): setLocale() rewrites that
 * config key, so re-reading it would restore the leaked value, not the
 * default. The shop portal is Arabic-only in v1.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale('ar');

        return $next($request);
    }
}
