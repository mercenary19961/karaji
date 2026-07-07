<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Persists a language choice (ar|en). Stored in the session for guest/auth
 * pages, and on the user record when authenticated so the shop portal
 * remembers it across devices (SetShopLocale reads user->locale).
 */
class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        if (in_array($locale, ['ar', 'en'], true)) {
            session(['locale' => $locale]);
            $request->user()?->update(['locale' => $locale]);
        }

        return back();
    }
}
