<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * The shop portal is Arabic-only in v1. Force 'ar' per request so the guest
 * language toggle (which may have parked 'en' in the session) can never leak
 * into the shop UI, whose strings are hardcoded Arabic. Mirror of
 * SetAdminLocale (which forces 'en').
 */
class SetShopLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale('ar');

        return $next($request);
    }
}
