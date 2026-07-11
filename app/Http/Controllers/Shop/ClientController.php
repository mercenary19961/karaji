<?php

namespace App\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The clients directory: every registered customer/car, most-recently-seen first
 * (so recent visitors and newcomers surface, not just overdue ones), searchable,
 * each opening the car profile where the info can be edited.
 */
class ClientController extends ShopController
{
    public function index(Request $request): Response
    {
        return Inertia::render('shop/clients', [
            'shop' => $this->shopProps($request),
            'clients' => $this->carIndex(),
        ]);
    }
}
