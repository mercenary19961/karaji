<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin portal is operator-only. Shop users land back on their portal.
 */
class EnsureAdminUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()->isAdmin()) {
            return redirect()->route('shop.dashboard');
        }

        return $next($request);
    }
}
