<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\ServiceType;
use App\Support\Format;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;

abstract class ShopController extends Controller
{
    /**
     * The `shop` prop every shop-portal page receives (header context).
     */
    protected function shopProps(Request $request): array
    {
        $shop = $request->user()->shop;

        // `name`/`area` follow the UI locale (header chrome); `nameAr` is always
        // the Arabic name for the customer-facing WhatsApp templates, which stay
        // Arabic regardless of the shop user's UI language.
        return [
            'name' => $shop->displayName(),
            'area' => $shop->displayArea(),
            'nameAr' => $shop->name,
        ];
    }

    /**
     * "آخر تغيير زيت: منذ ٤ أشهر على عداد 82,500 كم" — or null when the car
     * has no oil-change visit yet.
     */
    protected function lastOilLine(Car $car): ?string
    {
        $visit = $car->visits()
            ->whereHas('services', fn ($query) => $query->where('name', ServiceType::OIL_CHANGE))
            ->latest('visited_at')
            ->first();

        if ($visit === null) {
            return null;
        }

        return 'آخر تغيير زيت قبل '.$visit->visited_at->locale('ar')->diffForHumans(syntax: CarbonInterface::DIFF_ABSOLUTE).' على عداد '.Format::km($visit->km).' كم';
    }
}
