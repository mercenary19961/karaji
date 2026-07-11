<?php

namespace App\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The "start a new visit" landing: instant search over the shop's cars (via the
 * shared carIndex), quick access to recent customers, and a shortcut to register
 * a brand-new one.
 */
class EntryController extends ShopController
{
    public function index(Request $request): Response
    {
        return Inertia::render('shop/entry', [
            'shop' => $this->shopProps($request),
            'cars' => $this->carIndex(),
        ]);
    }
}
