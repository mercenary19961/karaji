<?php

namespace App\Http\Controllers\Shop;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends ShopController
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('shop/account', [
            'shop' => $this->shopProps($request),
            'account' => ['name' => $user->name, 'email' => $user->email],
            'autoAccept' => $user->shop->auto_accept_registrations,
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'auto_accept_registrations' => ['required', 'boolean'],
        ]);

        $request->user()->shop->update(['auto_accept_registrations' => $validated['auto_accept_registrations']]);

        return back()->with('success', __('shop.settings_saved'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        // Explicit min:8 (not Password::defaults) to keep the localized message.
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => __('shop.current_password_required'),
            'current_password.current_password' => __('shop.current_password_wrong'),
            'password.required' => __('shop.new_password_required'),
            'password.min' => __('shop.password_min'),
            'password.confirmed' => __('shop.password_mismatch'),
        ]);

        $request->user()->update(['password' => Hash::make($request->string('password')->value())]);

        return back()->with('success', __('shop.password_changed'));
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        // `image` validates by MIME/content; `mimes` locks the extension —
        // both, per the upload-hardening rule.
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'avatar.required' => __('shop.avatar_required'),
            'avatar.image' => __('shop.avatar_image'),
            'avatar.mimes' => __('shop.avatar_mimes'),
            'avatar.max' => __('shop.avatar_max'),
        ]);

        $path = $request->file('avatar')->store('avatars', 'public');
        $request->user()->setAvatar($path);

        return back()->with('success', __('shop.avatar_changed'));
    }

    public function deleteAvatar(Request $request): RedirectResponse
    {
        $request->user()->removeAvatar();

        return back()->with('success', __('shop.avatar_removed'));
    }
}
