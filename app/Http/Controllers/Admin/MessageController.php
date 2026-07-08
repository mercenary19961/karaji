<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /** Send a direct message to one shop (composed on the shop detail page). */
    public function store(Request $request, Shop $shop): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $shop->messages()->create($validated);

        return back()->with('success', 'Message sent');
    }
}
