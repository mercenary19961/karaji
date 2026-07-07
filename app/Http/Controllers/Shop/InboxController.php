<?php

namespace App\Http\Controllers\Shop;

use App\Models\Message;
use App\Models\Suggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The shop's communication centre: messages received from the admin (marked
 * read on view) and suggestions the shop sends back to the admin.
 */
class InboxController extends ShopController
{
    public function index(Request $request): Response
    {
        $shopId = $request->user()->shop_id;

        $messages = Message::query()->where('shop_id', $shopId)->latest()->get();

        // Opening the inbox marks everything read (single shared shop login).
        Message::query()->where('shop_id', $shopId)->whereNull('read_at')->update(['read_at' => now()]);

        $suggestions = Suggestion::query()->latest()->get();

        return Inertia::render('shop/messages', [
            'shop' => $this->shopProps($request),
            'messages' => $messages->map(fn (Message $m) => [
                'id' => $m->id,
                'title' => $m->title,
                'body' => $m->body,
                'date' => $m->created_at->format('d/m/Y'),
                'unread' => $m->read_at === null,
            ]),
            'suggestions' => $suggestions->map(fn (Suggestion $s) => [
                'id' => $s->id,
                'body' => $s->body,
                'status' => $s->status,
                'date' => $s->created_at->format('d/m/Y'),
            ]),
        ]);
    }

    public function storeSuggestion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'body.required' => __('shop.suggestion_required'),
        ]);

        // shop_id auto-filled by the BelongsToShop trait
        Suggestion::create(['body' => $validated['body']]);

        return back()->with('success', __('shop.suggestion_sent'));
    }
}
