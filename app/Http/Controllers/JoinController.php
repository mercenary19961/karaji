<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public (no-auth) customer self-registration reached by scanning the shop's QR.
 * Submissions either become customers immediately (auto-accept) or queue as
 * PendingRegistrations for the shop owner to accept. Customer-facing = Arabic.
 */
class JoinController extends Controller
{
    public function show(string $token): Response
    {
        $shop = $this->resolveShop($token);

        return Inertia::render('public/join', [
            'shopName' => $shop->name, // Arabic — the customer sees the Arabic name
            'token' => $token,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $shop = $this->resolveShop($token);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'plate' => ['required', 'string', 'max:20'],
            'label' => ['nullable', 'string', 'max:120'],
        ], [
            'name.required' => 'لازم تكتب اسمك',
            'phone.required' => 'لازم رقم التلفون',
            'plate.required' => 'لازم رقم اللوحة',
        ]);

        if ($shop->auto_accept_registrations) {
            $shop->registerCar($validated['name'], $validated['phone'], $validated['plate'], $validated['label'] ?? null);
        } else {
            $shop->pendingRegistrations()->create($validated);
        }

        return redirect()->route('join.show', $token)->with('success', 'وصلنا طلبك · شكراً إلك 🙏');
    }

    private function resolveShop(string $token): Shop
    {
        return Shop::query()->where('public_token', $token)->firstOrFail();
    }
}
