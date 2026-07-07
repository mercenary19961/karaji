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
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        // Explicit min:8 (not Password::defaults) so the messages stay Arabic.
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'اكتب كلمة المرور الحالية',
            'current_password.current_password' => 'كلمة المرور الحالية غلط',
            'password.required' => 'اكتب كلمة المرور الجديدة',
            'password.min' => 'كلمة المرور لازم تكون 8 أحرف عالأقل',
            'password.confirmed' => 'التأكيد مش مطابق',
        ]);

        $request->user()->update(['password' => Hash::make($request->string('password')->value())]);

        return back()->with('success', 'تم تغيير كلمة المرور');
    }
}
