<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Set the request locale at the start of EVERY web request. Runs explicitly
 * (not just the bootstrap default) because App::setLocale() mutates process
 * state, so in worker-mode servers (FrankenPHP/Octane) — and across requests
 * inside one test — a locale set for one request leaks into the next.
 *
 * Default is Arabic; the guest language toggle (LocaleController) may set a
 * session choice for the shared auth pages. The portals override this
 * afterwards via route middleware — SetShopLocale forces 'ar', SetAdminLocale
 * forces 'en' — so the toggle only ever affects login/register.
 *
 * Hardcoded 'ar' fallback, NOT config('app.locale'): setLocale() rewrites that
 * config key, so re-reading it would restore a leaked value, not the default.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');
        App::setLocale(in_array($locale, ['ar', 'en'], true) ? $locale : 'ar');

        return $next($request);
    }
}
