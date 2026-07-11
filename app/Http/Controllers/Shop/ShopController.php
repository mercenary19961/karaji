<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\ServiceType;
use App\Support\Format;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

abstract class ShopController extends Controller
{
    // A local garage's car count is small, so the whole directory is sent to the
    // client for zero-latency typeahead; capped so a large shop stays bounded.
    private const CAR_INDEX_CAP = 500;

    /**
     * The shop's car directory (customer + car + last visit), most-recently-seen
     * first. Shared by the entry typeahead and the clients page.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function carIndex(): Collection
    {
        return Car::query()
            ->with(['customer', 'latestVisit'])
            ->get()
            ->sortByDesc(fn (Car $car) => $car->latestVisit?->visited_at ?? $car->created_at)
            ->take(self::CAR_INDEX_CAP)
            ->map(fn (Car $car) => [
                'id' => $car->id,
                'plate' => $car->plate,
                'label' => $car->displayLabel(),
                'owner' => $car->customer->displayName(),
                'phone' => $car->customer->phone,
                'lastVisit' => $car->latestVisit?->visited_at->format('d/m/Y'),
            ])
            ->values();
    }

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
     * "آخر تغيير زيت قبل ٤ أشهر على عداد 82,500 كم" (or the English equivalent) —
     * null when the car has no oil-change visit yet.
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

        $km = Format::km($visit->km);

        if (App::getLocale() === 'en') {
            $ago = $visit->visited_at->locale('en')->diffForHumans(syntax: CarbonInterface::DIFF_ABSOLUTE);

            return "Last oil change {$ago} ago at {$km} km";
        }

        $ago = $visit->visited_at->locale('ar')->diffForHumans(syntax: CarbonInterface::DIFF_ABSOLUTE);

        return "آخر تغيير زيت قبل {$ago} على عداد {$km} كم";
    }
}
