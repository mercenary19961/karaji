<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * The admin portal is English/LTR while the app default is Arabic/RTL.
 * Setting the locale per-request flips lang/dir/font automatically via
 * app.blade.php (dir attribute) and the html[dir] font token in app.css.
 *
 * Note: the flip happens on full page loads. Client-side Inertia visits
 * keep the current <html> attributes — fine while the two portals are
 * entered separately, revisit if cross-portal in-app links ever exist.
 */
class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale('en');

        return $next($request);
    }
}
