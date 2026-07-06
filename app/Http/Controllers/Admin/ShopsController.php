<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Shop;
use App\Models\Subscription;
use Inertia\Inertia;
use Inertia\Response;

class ShopsController extends Controller
{
    public function index(): Response
    {
        $shops = Shop::query()
            ->with(['currentSubscription', 'latestVisit'])
            ->withCount(['visits' => fn ($query) => $query->where('visited_at', '>=', now()->startOfMonth())])
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/shops', [
            'shops' => $shops->map(fn (Shop $shop) => [
                'id' => $shop->id,
                'name' => $shop->name,
                'area' => $shop->area,
                'status' => $shop->currentSubscription?->status,
                'visits' => $shop->visits_count,
                'lastActive' => $shop->latestVisit === null
                    ? 'No visits yet'
                    : $shop->latestVisit->visited_at->locale('en')->diffForHumans(),
            ]),
        ]);
    }

    public function show(Shop $shop): Response
    {
        $shop->load('currentSubscription');
        $subscription = $shop->currentSubscription;

        $activity = ActivityLog::query()
            ->where('shop_id', $shop->id)
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('admin/shop-detail', [
            'shop' => [
                'id' => $shop->id,
                'name' => $shop->name,
                'area' => $shop->area,
                'stats' => [
                    ['label' => 'Visits this month', 'value' => $shop->visits()->where('visited_at', '>=', now()->startOfMonth())->count()],
                    ['label' => 'Cars on file', 'value' => $shop->cars()->count()],
                    ['label' => 'Customers', 'value' => $shop->customers()->count()],
                    ['label' => 'Reminders contacted', 'value' => $shop->reminders()->where('status', 'contacted')->count()],
                ],
                'subscription' => $subscription === null ? null : [
                    'status' => $subscription->status,
                    'plan' => $subscription->plan,
                    'plans' => collect(Subscription::PLANS)->map(fn (array $plan, string $key) => ['key' => $key, 'label' => $plan['label']])->values(),
                    'renewsAt' => $subscription->renews_at?->format('M j, Y'),
                    'trialEndsAt' => $subscription->trial_ends_at?->format('M j, Y'),
                ],
                'activity' => $activity->map(fn (ActivityLog $log) => [
                    'id' => $log->id,
                    'text' => $log->action,
                    'at' => $log->created_at->format('M j, H:i'),
                    'undoable' => $log->isUndoable(),
                    'undone' => $log->undone_at !== null,
                ]),
            ],
        ]);
    }
}
