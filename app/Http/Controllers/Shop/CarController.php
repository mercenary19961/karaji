<?php

namespace App\Http\Controllers\Shop;

use App\Models\Car;
use App\Models\Visit;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CarController extends ShopController
{
    /**
     * The counter moment: last digits of a plate OR a phone number.
     * `to=visit` sends the match to the new-visit form instead of the profile.
     */
    public function search(Request $request): RedirectResponse
    {
        $validated = $request->validate(['q' => ['required', 'string', 'max:30']], [
            'q.required' => 'اكتب رقم اللوحة أو التلفون',
        ]);

        $q = trim($validated['q']);

        $car = Car::query()->where('plate', 'like', "%{$q}%")->first()
            ?? Car::query()->whereHas('customer', fn ($query) => $query->where('phone', 'like', "%{$q}%"))->first();

        if ($car === null) {
            return back()->with('error', 'ما لقينا سيارة بهالرقم · جرّب رقم ثاني أو سجّلها كسيارة جديدة');
        }

        return $request->query('to') === 'visit'
            ? redirect()->route('shop.visits.create', ['car' => $car->id])
            : redirect()->route('shop.cars.show', $car);
    }

    public function show(Request $request, Car $car): Response
    {
        $car->load(['customer', 'pendingOilReminder', 'visits' => fn ($query) => $query->latest('visited_at')->with('services')]);

        $nextDue = $car->pendingOilReminder;

        return Inertia::render('shop/car', [
            'shop' => $this->shopProps($request),
            'car' => [
                'id' => $car->id,
                'label' => $car->label ?? $car->plate,
                'plate' => $car->plate,
                'owner' => $car->customer->name,
                'phone' => $car->customer->phone,
                'whatsapp' => $car->customer->whatsappNumber(),
                'lastService' => $this->lastOilLine($car),
                'licenseMonth' => $car->licenseMonthLabel(),
                'nextDue' => $nextDue === null ? null : [
                    'km' => $nextDue->due_km === null ? null : Format::km($nextDue->due_km),
                    'date' => $nextDue->due_date?->format('d/m/Y'),
                ],
                'visits' => $car->visits->map(fn (Visit $visit) => [
                    'date' => $visit->visited_at->format('d/m/Y'),
                    'km' => Format::km($visit->km),
                    'price' => Format::price($visit->price),
                    'services' => $visit->services->pluck('name'),
                ]),
            ],
        ]);
    }
}
