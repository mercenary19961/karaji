<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Subscription;
use App\Support\AdminActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    /**
     * Change plan and/or status. Every change is activity-logged with its
     * before-state so it can be undone from the activity list.
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

        $before = array_intersect_key($subscription->getOriginal(), $validated);

        $subscription->update($validated);

        if (! $subscription->wasChanged()) {
            return back();
        }

        AdminActivity::log($shop->id, $this->describeChange($subscription, $validated), $subscription, $before, $validated);

        return back()->with('success', 'Subscription updated');
    }

    public function extendTrial(Shop $shop): RedirectResponse
    {
        $subscription = $shop->currentSubscription;
        abort_if($subscription === null, 404);

        $before = [
            'status' => $subscription->status,
            'trial_ends_at' => $subscription->trial_ends_at?->toDateString(),
        ];

        $base = $subscription->trial_ends_at !== null && $subscription->trial_ends_at->isFuture()
            ? $subscription->trial_ends_at
            : today();
        $newEnd = $base->copy()->addMonth();

        $subscription->update(['status' => 'trial', 'trial_ends_at' => $newEnd->toDateString()]);

        AdminActivity::log(
            $shop->id,
            'Trial extended to '.$newEnd->format('M j, Y'),
            $subscription,
            $before,
            ['status' => 'trial', 'trial_ends_at' => $newEnd->toDateString()],
        );

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
