<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * The shop portal is bilingual (ar default, en optional). Set the locale from
 * the user's stored preference on every request so worker-mode leaks can't
 * cross requests, and so the guest session toggle never overrides a shop
 * user's saved choice. Mirror of SetAdminLocale (which forces 'en').
 */
class SetShopLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($request->user()?->locale ?? 'ar');

        return $next($request);
    }
}
