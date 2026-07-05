<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\ShopDemoData;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Shop portal screens, currently fed by ShopDemoData (static demo props).
 * At schema v1 these methods swap the demo arrays for real queries — the
 * prop shapes stay the same.
 */
class ShopScreensController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('shop/dashboard', [
            'shop' => ShopDemoData::shop(),
            'stats' => ShopDemoData::stats(),
            'dueToday' => ShopDemoData::dueToday(),
        ]);
    }

    public function createVisit(): Response
    {
        return Inertia::render('shop/new-visit', [
            'shop' => ShopDemoData::shop(),
            'car' => ShopDemoData::car(),
            'serviceTypes' => ShopDemoData::serviceTypes(),
            'oilBrands' => ShopDemoData::oilBrands(),
        ]);
    }

    public function showCar(): Response
    {
        return Inertia::render('shop/car', [
            'shop' => ShopDemoData::shop(),
            'car' => ShopDemoData::car(),
        ]);
    }

    public function reminders(): Response
    {
        return Inertia::render('shop/reminders', [
            'shop' => ShopDemoData::shop(),
            'reminders' => ShopDemoData::reminders(),
        ]);
    }

    public function analytics(): Response
    {
        return Inertia::render('shop/analytics', [
            'shop' => ShopDemoData::shop(),
            'analytics' => ShopDemoData::analytics(),
        ]);
    }
}
