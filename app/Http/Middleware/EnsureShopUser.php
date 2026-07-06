<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shop portal routes need a user attached to a shop. Admins (shop_id null)
 * land on the admin portal instead — real "Login as shop" impersonation
 * comes with the roles/impersonation work.
 */
class EnsureShopUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->shop_id === null) {
            return redirect()->route('admin.shops.index');
        }

        return $next($request);
    }
}
