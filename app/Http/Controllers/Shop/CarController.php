<?php

namespace App\Http\Controllers\Shop;

use App\Models\Car;
use App\Models\Visit;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            'q.required' => __('shop.search_required'),
        ]);

        $q = trim($validated['q']);

        $car = Car::query()->where('plate', 'like', "%{$q}%")->first()
            ?? Car::query()->whereHas('customer', fn ($query) => $query->where('phone', 'like', "%{$q}%"))->first();

        if ($car === null) {
            return back()->with('error', __('shop.search_not_found'));
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
                'label' => $car->displayLabel(),
                'labelAr' => $car->labelAr(), // for the Arabic WhatsApp greeting
                'plate' => $car->plate,
                'owner' => $car->customer->displayName(),
                'ownerAr' => $car->customer->name,
                'phone' => $car->customer->phone,
                'whatsapp' => $car->customer->whatsappNumber(),
                'lastService' => $this->lastOilLine($car),
                'licenseMonth' => $car->licenseMonthLabel(),
                'nextDue' => $nextDue === null ? null : [
                    'km' => $nextDue->due_km === null ? null : Format::km($nextDue->due_km),
                    'date' => $nextDue->due_date?->format('d/m/Y'),
                ],
                'visits' => $car->visits->map(fn (Visit $visit) => [
                    'id' => $visit->id,
                    'date' => $visit->visited_at->format('d/m/Y'),
                    'km' => Format::km($visit->km),
                    'price' => Format::price($visit->revenue()),
                    'services' => $visit->services->map(fn ($service) => $service->displayName()),
                ]),
            ],
        ]);
    }

    /**
     * Edit a client's details — e.g. after accepting a QR self-registration the
     * owner tidies the name/plate or adds the license month the form didn't ask for.
     */
    public function edit(Request $request, Car $car): Response
    {
        $car->load('customer');

        return Inertia::render('shop/edit-client', [
            'shop' => $this->shopProps($request),
            'client' => [
                'id' => $car->id,
                // Raw Arabic values (the shop enters Arabic; *_en is seed-only)
                'name' => $car->customer->name,
                'phone' => $car->customer->phone,
                'plate' => $car->plate,
                'label' => $car->label,
                'licenseMonth' => $car->license_month,
            ],
            'licenseMonths' => collect(range(1, 12))
                ->map(fn (int $month) => ['value' => $month, 'label' => Format::monthName($month)])
                ->values(),
        ]);
    }

    public function update(Request $request, Car $car): RedirectResponse
    {
        $shopId = $request->user()->shop_id;
        $car->load('customer');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('customers')->where('shop_id', $shopId)->ignore($car->customer_id)],
            'plate' => ['required', 'string', 'max:20', Rule::unique('cars')->where('shop_id', $shopId)->ignore($car->id)],
            'label' => ['nullable', 'string', 'max:120'],
            'license_month' => ['nullable', 'integer', 'between:1,12'],
        ], [
            'name.required' => __('shop.name_required'),
            'phone.required' => __('shop.phone_required'),
            'phone.unique' => __('shop.phone_unique'),
            'plate.required' => __('shop.plate_required'),
            'plate.unique' => __('shop.plate_unique'),
        ]);

        $car->customer->update(['name' => $validated['name'], 'phone' => $validated['phone']]);
        $car->update([
            'plate' => $validated['plate'],
            'label' => $validated['label'] ?? null,
            'license_month' => $validated['license_month'] ?? null,
        ]);

        return redirect()->route('shop.cars.show', $car->id)->with('success', __('shop.client_updated'));
    }
}
