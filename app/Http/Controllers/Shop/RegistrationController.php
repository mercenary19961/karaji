<?php

namespace App\Http\Controllers\Shop;

use App\Models\PendingRegistration;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The shop owner's side of QR self-registration: the QR/link to display, and
 * the pending queue to accept (→ creates the customer + car) or reject.
 */
class RegistrationController extends ShopController
{
    public function index(Request $request): Response
    {
        $shop = $request->user()->shop;

        // BelongsToShop scopes this to the shop automatically
        $pending = PendingRegistration::query()
            ->latest()
            ->get()
            ->map(fn (PendingRegistration $reg) => [
                'id' => $reg->id,
                'name' => $reg->name,
                'phone' => $reg->phone,
                'plate' => $reg->plate,
                'label' => $reg->label,
                'licenseMonth' => $reg->license_month === null ? null : Format::monthName($reg->license_month),
                'ago' => $reg->created_at->locale(app()->getLocale())->diffForHumans(),
            ]);

        return Inertia::render('shop/registrations', [
            'shop' => $this->shopProps($request),
            'joinUrl' => route('join.show', $shop->public_token),
            'autoAccept' => $shop->auto_accept_registrations,
            'pending' => $pending,
        ]);
    }

    public function accept(Request $request, PendingRegistration $pendingRegistration): RedirectResponse
    {
        $car = $request->user()->shop->registerCar(
            $pendingRegistration->name,
            $pendingRegistration->phone,
            $pendingRegistration->plate,
            $pendingRegistration->label,
            $pendingRegistration->license_month,
        );

        $pendingRegistration->delete();

        // Straight into a visit for the just-added car (info pre-filled) — the
        // owner enters the services done and can edit the details from there.
        return redirect()
            ->route('shop.visits.create', ['car' => $car->id])
            ->with('success', __('shop.reg_accepted'));
    }

    public function reject(PendingRegistration $pendingRegistration): RedirectResponse
    {
        $pendingRegistration->delete();

        return back()->with('success', __('shop.reg_rejected'));
    }
}
