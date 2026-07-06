<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "Login as shop" — support IS the product for non-technical users. The
 * admin's id is kept in the session so the banner in the shop portal can
 * return them to the admin portal.
 */
class ImpersonationController extends Controller
{
    public function store(Request $request, Shop $shop): RedirectResponse
    {
        $target = $shop->users()->first();

        if ($target === null) {
            return back()->with('error', 'This shop has no user account yet');
        }

        $request->session()->put('impersonator_id', $request->user()->id);
        Auth::login($target);

        return redirect()->route('shop.dashboard');
    }

    /**
     * Reachable from the shop portal while impersonating (route lives outside
     * the admin group — the current user is a shop user at that point).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $admin = User::find($request->session()->pull('impersonator_id'));

        if ($admin === null || ! $admin->isAdmin()) {
            return redirect()->route('home');
        }

        Auth::login($admin);

        return redirect()->route('admin.shops.index');
    }
}
