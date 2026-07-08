<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Subscription;
use App\Services\ChangeLog\ChangeLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function __construct(private readonly ChangeLogService $changeLog) {}

    /**
     * Change plan and/or status. Every change is logged with dirty-field
     * snapshots so it can be reverted (with conflict detection) from the
     * activity list.
     */
    public function update(Request $request, Shop $shop): RedirectResponse
    {
        $subscription = $shop->currentSubscription;
        abort_if($subscription === null, 404);

        $validated = $request->validate([
            'plan' => ['sometimes', Rule::in(array_keys(Subscription::PLANS))],
            'status' => ['sometimes', Rule::in(Subscription::STATUSES)],
        ]);

        if ($validated === []) {
            return back();
        }

        if (isset($validated['plan'])) {
            $validated['price_jod'] = Subscription::PLANS[$validated['plan']]['price'];
        }

        $before = $subscription->attributesToArray();
        $subscription->update($validated);

        if (! $subscription->wasChanged()) {
            return back();
        }

        $this->changeLog->logUpdated($subscription, $before, $this->describeChange($subscription, $validated));

        return back()->with('success', 'Subscription updated');
    }

    public function extendTrial(Shop $shop): RedirectResponse
    {
        $subscription = $shop->currentSubscription;
        abort_if($subscription === null, 404);

        $base = $subscription->trial_ends_at !== null && $subscription->trial_ends_at->isFuture()
            ? $subscription->trial_ends_at
            : today();
        $newEnd = $base->copy()->addMonth();

        $before = $subscription->attributesToArray();
        $subscription->update(['status' => 'trial', 'trial_ends_at' => $newEnd->toDateString()]);

        $this->changeLog->logUpdated($subscription, $before, 'Trial extended to '.$newEnd->format('M j, Y'));

        return back()->with('success', 'Trial extended');
    }

    private function describeChange(Subscription $subscription, array $validated): string
    {
        if (isset($validated['plan'])) {
            return 'Plan changed to '.$subscription->planLabel();
        }

        return match ($validated['status']) {
            'suspended' => 'Shop suspended',
            'active' => 'Shop activated',
            default => 'Moved to trial',
        };
    }
}
