<?php

namespace App\Http\Controllers\Shop;

use App\Models\Car;
use App\Models\Customer;
use App\Models\ServicePrice;
use App\Models\ServiceType;
use App\Models\Visit;
use App\Services\Reminders\ReminderEngine;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VisitController extends ShopController
{
    // v1: static list; "remembered from last visit" comes from the car's history
    private const OIL_BRANDS = ['Mobil 5W-30', 'Castrol 5W-40', 'Total 10W-40', 'Shell 5W-30', 'آخر'];

    // Oil-type chip values; labels are localized at render time
    private const OIL_TYPES = ['mineral', 'synthetic'];

    public function __construct(private readonly ReminderEngine $engine) {}

    public function create(Request $request): Response
    {
        $shopId = $request->user()->shop_id;

        $defaultPrices = ServicePrice::query()->pluck('price', 'service_type_id');

        $car = $request->query('car') === null
            ? null
            : Car::query()->with('customer', 'latestVisit')->findOrFail((int) $request->query('car'));

        $saved = $request->query('saved') === null
            ? null
            : Visit::query()->with(['services', 'car.customer', 'car.pendingOilReminder'])->findOrFail((int) $request->query('saved'));

        return Inertia::render('shop/new-visit', [
            'shop' => $this->shopProps($request),
            // Arriving from the entry page's "new customer" shortcut opens the
            // form straight in new-customer mode.
            'startNew' => $car === null && $request->query('new') !== null,
            'car' => $car === null ? null : [
                'id' => $car->id,
                'label' => $car->displayLabel(),
                'plate' => $car->plate,
                'owner' => $car->customer->displayName(),
                'phone' => $car->customer->phone,
                'lastService' => $this->lastOilLine($car),
                'lastOilBrand' => $car->latestVisit?->oil_brand,
                'lastOilType' => $car->latestVisit?->oil_type,
            ],
            // `name` is the stable Arabic key (matched in the form + used in the
            // Arabic WhatsApp summary); `label` is the localized chip caption;
            // `defaultPrice` pre-fills the per-service price box (null = unpriced).
            'serviceTypes' => ServiceType::availableToShop($shopId)->get(['id', 'name', 'name_en'])
                ->map(fn (ServiceType $s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'label' => $s->displayName(),
                    'defaultPrice' => $defaultPrices->get($s->id) === null ? null : (string) (float) $defaultPrices->get($s->id),
                ]),
            'oilBrands' => self::OIL_BRANDS,
            'oilTypes' => collect(self::OIL_TYPES)->map(fn (string $key) => ['key' => $key, 'label' => __("shop.oil_{$key}")])->values(),
            'savedVisit' => $saved === null ? null : [
                'id' => $saved->id,
                'carId' => $saved->car->id,
                // The success card shows the localized label; the WhatsApp summary
                // (customer-facing) is built from the *Ar fields so it stays Arabic.
                'carLabel' => $saved->car->displayLabel(),
                'carLabelAr' => $saved->car->labelAr(),
                'plate' => $saved->car->plate,
                'owner' => $saved->car->customer->displayName(),
                'ownerAr' => $saved->car->customer->name,
                'whatsapp' => $saved->car->customer->whatsappNumber(),
                'km' => Format::km($saved->km),
                'services' => $saved->services->pluck('name'), // Arabic — goes into the WhatsApp summary
                'oilBrand' => $saved->oil_brand,
                'nextDueKm' => $saved->car->pendingOilReminder?->due_km === null ? null : Format::km($saved->car->pendingOilReminder->due_km),
                'nextDueDate' => $saved->car->pendingOilReminder?->due_date?->format('d/m/Y'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $shopId = $request->user()->shop_id;

        $validated = $request->validate([
            'car_id' => ['nullable', 'integer'],
            'name' => ['required_without:car_id', 'nullable', 'string', 'max:120'],
            'phone' => ['required_without:car_id', 'nullable', 'string', 'max:20'],
            'plate' => [
                'required_without:car_id',
                'nullable',
                'string',
                'max:20',
                Rule::unique('cars')->where('shop_id', $shopId),
            ],
            'label' => ['nullable', 'string', 'max:120'],
            'km' => ['required', 'integer', 'min:0', 'max:2000000'],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['integer'],
            'oil_brand' => ['nullable', 'string', 'max:60'],
            'oil_type' => ['nullable', Rule::in(self::OIL_TYPES)],
            // Per-service prices, keyed by service id (empty ones sent as null)
            'prices' => ['array'],
            'prices.*' => ['nullable', 'numeric', 'min:0', 'max:99999'],
        ], [
            'km.required' => __('shop.km_required'),
            'km.integer' => __('shop.km_number'),
            'services.required' => __('shop.services_required'),
            'name.required_without' => __('shop.name_required'),
            'phone.required_without' => __('shop.phone_required'),
            'plate.required_without' => __('shop.plate_required'),
            'plate.unique' => __('shop.plate_unique'),
        ]);

        $serviceIds = ServiceType::availableToShop($shopId)
            ->whereIn('id', $validated['services'])
            ->pluck('id');

        if ($serviceIds->count() !== count(array_unique($validated['services']))) {
            throw ValidationException::withMessages(['services' => __('shop.service_invalid')]);
        }

        // Oil type only matters when the visit includes an oil change; default
        // it so a first-timer's reminder still uses a sane interval.
        $isOilChange = ServiceType::query()->whereIn('id', $serviceIds)->where('name', ServiceType::OIL_CHANGE)->exists();
        $oilType = $isOilChange ? ($validated['oil_type'] ?? ReminderEngine::DEFAULT_OIL_TYPE) : null;

        $car = $this->resolveCar($validated);

        $visit = $car->visits()->make([
            'km' => $validated['km'],
            'oil_brand' => $validated['oil_brand'] ?? null,
            'oil_type' => $oilType,
            'visited_at' => now(),
        ]);
        $visit->save();

        // Each service's charged price = the per-visit override if given, else
        // the shop's saved default (may be null → recorded without a price).
        $defaultPrices = ServicePrice::query()->pluck('price', 'service_type_id');
        $submitted = $validated['prices'] ?? [];

        $visit->services()->attach($serviceIds->mapWithKeys(function (int $id) use ($submitted, $defaultPrices) {
            $override = $submitted[$id] ?? null;
            $price = is_numeric($override) ? $override : $defaultPrices->get($id);

            return [$id => ['price' => $price]];
        })->all());

        $this->engine->scheduleOilReminder($car);

        return redirect()
            ->route('shop.visits.create', ['car' => $car->id, 'saved' => $visit->id])
            ->with('success', __('shop.visit_saved'));
    }

    public function edit(Request $request, Visit $visit): Response
    {
        $shopId = $request->user()->shop_id;
        $visit->load(['car.customer', 'services']);

        $defaultPrices = ServicePrice::query()->pluck('price', 'service_type_id');

        return Inertia::render('shop/edit-visit', [
            'shop' => $this->shopProps($request),
            'visit' => [
                'id' => $visit->id,
                'carId' => $visit->car_id,
                'carLabel' => $visit->car->displayLabel(),
                'plate' => $visit->car->plate,
                'owner' => $visit->car->customer->displayName(),
                'date' => $visit->visited_at->format('d/m/Y'),
                'km' => (string) $visit->km,
                'oilBrand' => $visit->oil_brand,
                'oilType' => $visit->oil_type,
                // Current services + the price charged for each
                'services' => $visit->services->map(fn (ServiceType $s) => [
                    'id' => $s->id,
                    'price' => $s->pivot->price === null ? '' : (string) (float) $s->pivot->price,
                ]),
            ],
            'serviceTypes' => ServiceType::availableToShop($shopId)->get(['id', 'name', 'name_en'])
                ->map(fn (ServiceType $s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'label' => $s->displayName(),
                    'defaultPrice' => $defaultPrices->get($s->id) === null ? null : (string) (float) $defaultPrices->get($s->id),
                ]),
            'oilBrands' => self::OIL_BRANDS,
            'oilTypes' => collect(self::OIL_TYPES)->map(fn (string $key) => ['key' => $key, 'label' => __("shop.oil_{$key}")])->values(),
        ]);
    }

    public function update(Request $request, Visit $visit): RedirectResponse
    {
        $shopId = $request->user()->shop_id;

        $validated = $request->validate([
            'km' => ['required', 'integer', 'min:0', 'max:2000000'],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['integer'],
            'oil_brand' => ['nullable', 'string', 'max:60'],
            'oil_type' => ['nullable', Rule::in(self::OIL_TYPES)],
            'prices' => ['array'],
            'prices.*' => ['nullable', 'numeric', 'min:0', 'max:99999'],
        ], [
            'km.required' => __('shop.km_required'),
            'km.integer' => __('shop.km_number'),
            'services.required' => __('shop.services_required'),
        ]);

        $serviceIds = ServiceType::availableToShop($shopId)
            ->whereIn('id', $validated['services'])
            ->pluck('id');

        if ($serviceIds->count() !== count(array_unique($validated['services']))) {
            throw ValidationException::withMessages(['services' => __('shop.service_invalid')]);
        }

        $isOilChange = ServiceType::query()->whereIn('id', $serviceIds)->where('name', ServiceType::OIL_CHANGE)->exists();
        $oilType = $isOilChange ? ($validated['oil_type'] ?? ReminderEngine::DEFAULT_OIL_TYPE) : null;

        $visit->update([
            'km' => $validated['km'],
            'oil_brand' => $validated['oil_brand'] ?? null,
            'oil_type' => $oilType,
        ]);

        // `sync` replaces the visit's services with the new set + charged prices
        $defaultPrices = ServicePrice::query()->pluck('price', 'service_type_id');
        $submitted = $validated['prices'] ?? [];

        $visit->services()->sync($serviceIds->mapWithKeys(function (int $id) use ($submitted, $defaultPrices) {
            $override = $submitted[$id] ?? null;
            $price = is_numeric($override) ? $override : $defaultPrices->get($id);

            return [$id => ['price' => $price]];
        })->all());

        // km / oil-type / services may have changed → re-derive the oil reminder
        $this->engine->scheduleOilReminder($visit->car);

        return redirect()->route('shop.cars.show', $visit->car_id)->with('success', __('shop.visit_updated'));
    }

    /**
     * Undo a just-saved visit (undo instead of confirm dialogs), then re-derive
     * the oil reminder from whatever history remains.
     */
    public function destroy(Visit $visit): RedirectResponse
    {
        $car = $visit->car;

        $visit->delete();
        $this->engine->scheduleOilReminder($car);

        return redirect()
            ->route('shop.visits.create', ['car' => $car->id])
            ->with('success', __('shop.visit_undone'));
    }

    private function resolveCar(array $validated): Car
    {
        if (isset($validated['car_id'])) {
            return Car::query()->findOrFail($validated['car_id']);
        }

        // New customers are matched by phone first — a second car for an
        // existing customer must not create a duplicate person.
        $customer = Customer::query()->firstOrCreate(
            ['phone' => $validated['phone']],
            ['name' => $validated['name']],
        );

        return Car::create([
            'customer_id' => $customer->id,
            'plate' => $validated['plate'],
            'label' => $validated['label'] ?? null,
        ]);
    }
}
