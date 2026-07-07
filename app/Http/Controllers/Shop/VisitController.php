<?php

namespace App\Http\Controllers\Shop;

use App\Models\Car;
use App\Models\Customer;
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

    // Oil-type chip value => Arabic label for the visit form
    private const OIL_TYPES = ['mineral' => 'زيت عادي', 'synthetic' => 'زيت صناعي'];

    public function __construct(private readonly ReminderEngine $engine) {}

    public function create(Request $request): Response
    {
        $shopId = $request->user()->shop_id;

        $car = $request->query('car') === null
            ? null
            : Car::query()->with('customer', 'latestVisit')->findOrFail((int) $request->query('car'));

        $saved = $request->query('saved') === null
            ? null
            : Visit::query()->with(['services', 'car.customer', 'car.pendingOilReminder'])->findOrFail((int) $request->query('saved'));

        return Inertia::render('shop/new-visit', [
            'shop' => $this->shopProps($request),
            'car' => $car === null ? null : [
                'id' => $car->id,
                'label' => $car->label ?? $car->plate,
                'plate' => $car->plate,
                'owner' => $car->customer->name,
                'phone' => $car->customer->phone,
                'lastService' => $this->lastOilLine($car),
                'lastOilBrand' => $car->latestVisit?->oil_brand,
                'lastOilType' => $car->latestVisit?->oil_type,
            ],
            'serviceTypes' => ServiceType::availableToShop($shopId)->get(['id', 'name']),
            'oilBrands' => self::OIL_BRANDS,
            'oilTypes' => collect(self::OIL_TYPES)->map(fn (string $label, string $key) => ['key' => $key, 'label' => $label])->values(),
            'savedVisit' => $saved === null ? null : [
                'id' => $saved->id,
                'carId' => $saved->car->id,
                'carLabel' => $saved->car->label ?? $saved->car->plate,
                'plate' => $saved->car->plate,
                'owner' => $saved->car->customer->name,
                'whatsapp' => $saved->car->customer->whatsappNumber(),
                'km' => Format::km($saved->km),
                'services' => $saved->services->pluck('name'),
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
            'oil_type' => ['nullable', Rule::in(array_keys(self::OIL_TYPES))],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999'],
        ], [
            'km.required' => 'لازم تكتب قراءة العداد',
            'km.integer' => 'قراءة العداد لازم تكون رقم',
            'services.required' => 'اختر خدمة وحدة عالأقل',
            'name.required_without' => 'لازم اسم الزبون',
            'phone.required_without' => 'لازم رقم التلفون',
            'plate.required_without' => 'لازم رقم اللوحة',
            'plate.unique' => 'هاللوحة مسجلة عندك من قبل · دوّرها من البحث',
        ]);

        $serviceIds = ServiceType::availableToShop($shopId)
            ->whereIn('id', $validated['services'])
            ->pluck('id');

        if ($serviceIds->count() !== count(array_unique($validated['services']))) {
            throw ValidationException::withMessages(['services' => 'في خدمة مش صالحة']);
        }

        // Oil type only matters when the visit includes an oil change; default
        // it so a first-timer's reminder still uses a sane interval.
        $isOilChange = ServiceType::query()->whereIn('id', $serviceIds)->where('name', ServiceType::OIL_CHANGE)->exists();
        $oilType = $isOilChange ? ($validated['oil_type'] ?? ReminderEngine::DEFAULT_OIL_TYPE) : null;

        $car = $this->resolveCar($validated);

        $visit = $car->visits()->make([
            'km' => $validated['km'],
            'price' => $validated['price'] ?? null,
            'oil_brand' => $validated['oil_brand'] ?? null,
            'oil_type' => $oilType,
            'visited_at' => now(),
        ]);
        $visit->save();
        $visit->services()->attach($serviceIds);

        $this->engine->scheduleOilReminder($car);

        return redirect()
            ->route('shop.visits.create', ['car' => $car->id, 'saved' => $visit->id])
            ->with('success', 'انحفظت الزيارة');
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
            ->with('success', 'رجّعنا الزيارة');
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
