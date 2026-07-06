<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/**
 * Persists a language choice for the guest/auth pages. The portals ignore it —
 * the shop portal is forced Arabic (SetShopLocale) and the admin portal forced
 * English (SetAdminLocale) — so this only steers the shared login/register flow.
 */
class LocaleController extends Controller
{
    public function __invoke(string $locale): RedirectResponse
    {
        if (in_array($locale, ['ar', 'en'], true)) {
            session(['locale' => $locale]);
        }

        return back();
    }
}
