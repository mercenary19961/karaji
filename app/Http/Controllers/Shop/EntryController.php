<?php

namespace App\Http\Controllers\Shop;

use App\Models\Car;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The "start a new visit" landing: instant search over the shop's cars, quick
 * access to recent customers, and a shortcut to register a brand-new one.
 *
 * The whole car index is sent to the client so the typeahead filters with zero
 * latency (no per-keystroke round-trip) — a local garage's car count is small.
 * Capped so a very large shop still gets a bounded payload; revisit with a
 * server-side search endpoint if that ever becomes the norm.
 */
class EntryController extends ShopController
{
    private const MAX_INDEX = 500;

    public function index(Request $request): Response
    {
        $cars = Car::query()
            ->with(['customer', 'latestVisit'])
            ->get()
            // Most-recently-seen first, so the client can slice the top for
            // "recent customers" and rank ties by recency.
            ->sortByDesc(fn (Car $car) => $car->latestVisit?->visited_at ?? $car->created_at)
            ->take(self::MAX_INDEX)
            ->map(fn (Car $car) => [
                'id' => $car->id,
                'plate' => $car->plate,
                'label' => $car->displayLabel(),
                'owner' => $car->customer->displayName(),
                'phone' => $car->customer->phone,
                'lastVisit' => $car->latestVisit?->visited_at->format('d/m/Y'),
            ])
            ->values();

        return Inertia::render('shop/entry', [
            'shop' => $this->shopProps($request),
            'cars' => $cars,
        ]);
    }
}
