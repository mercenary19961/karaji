<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Suggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SuggestionController extends Controller
{
    public function index(): Response
    {
        // Admin (shop_id null) is unscoped by BelongsToShop, so this is all shops
        $suggestions = Suggestion::query()
            ->with('shop:id,name')
            ->orderByRaw("status = 'open' desc")
            ->latest()
            ->get();

        return Inertia::render('admin/suggestions', [
            'suggestions' => $suggestions->map(fn (Suggestion $s) => [
                'id' => $s->id,
                'shop' => $s->shop?->name ?? '—',
                'body' => $s->body,
                'status' => $s->status,
                'date' => $s->created_at->format('M j, Y'),
            ]),
        ]);
    }

    public function update(Request $request, Suggestion $suggestion): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([Suggestion::STATUS_OPEN, Suggestion::STATUS_REVIEWED])],
        ]);

        $suggestion->update($validated);

        return back()->with('success', 'Suggestion updated');
    }
}
