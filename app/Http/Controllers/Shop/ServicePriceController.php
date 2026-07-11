<?php

namespace App\Http\Controllers\Shop;

use App\Models\ServicePrice;
use App\Models\ServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The shop's default charge per service (its "profile" pricing). These pre-fill
 * the per-service prices on the visit form; they can still be overridden per visit.
 */
class ServicePriceController extends ShopController
{
    public function edit(Request $request): Response
    {
        $shopId = $request->user()->shop_id;

        $prices = ServicePrice::query()->pluck('price', 'service_type_id');

        $services = ServiceType::availableToShop($shopId)->get(['id', 'name', 'name_en'])
            ->map(fn (ServiceType $service) => [
                'id' => $service->id,
                'label' => $service->displayName(),
                'price' => $prices->get($service->id) === null ? '' : (string) (float) $prices->get($service->id),
            ]);

        return Inertia::render('shop/service-prices', [
            'shop' => $this->shopProps($request),
            'services' => $services,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $shopId = $request->user()->shop_id;

        $validated = $request->validate([
            'prices' => ['array'],
            'prices.*' => ['nullable', 'numeric', 'min:0', 'max:99999'],
        ]);

        // Only accept prices for services this shop is actually allowed to use
        $allowed = ServiceType::availableToShop($shopId)->pluck('id');

        foreach ($validated['prices'] ?? [] as $serviceTypeId => $price) {
            if (! $allowed->contains((int) $serviceTypeId)) {
                continue;
            }

            // A cleared box removes the default (no forced price)
            if ($price === null || $price === '') {
                ServicePrice::query()->where('service_type_id', $serviceTypeId)->delete();

                continue;
            }

            ServicePrice::query()->updateOrCreate(
                ['service_type_id' => (int) $serviceTypeId],
                ['price' => $price],
            );
        }

        return back()->with('success', __('shop.prices_saved'));
    }
}
