<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(): Response
    {
        $announcements = Announcement::query()
            ->with('shop:id,name')
            ->latest()
            ->get();

        return Inertia::render('admin/announcements', [
            'announcements' => $announcements->map(fn (Announcement $a) => [
                'id' => $a->id,
                'title' => $a->title,
                'body' => $a->body,
                'target' => $a->isBroadcast() ? 'All shops' : ($a->shop?->name ?? 'Unknown shop'),
                'isActive' => $a->is_active,
                'startsAt' => $a->starts_at?->format('M j, Y'),
                'endsAt' => $a->ends_at?->format('M j, Y'),
                'createdAt' => $a->created_at->format('M j, Y'),
            ]),
            // Broadcast is the default (empty value); the rest target one shop.
            'shops' => Shop::query()->orderBy('name')->get(['id', 'name']),
            // Prewritten seasonal templates (bilingual) to pre-fill the form.
            'templates' => config('announcement_templates'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'title_en' => ['nullable', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:2000'],
            'body_en' => ['nullable', 'string', 'max:2000'],
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        Announcement::create([...$validated, 'is_active' => true]);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement published');
    }

    public function toggle(Announcement $announcement): RedirectResponse
    {
        $announcement->update(['is_active' => ! $announcement->is_active]);

        return back()->with('success', $announcement->is_active ? 'Announcement activated' : 'Announcement paused');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('success', 'Announcement deleted');
    }
}
