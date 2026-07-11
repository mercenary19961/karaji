<?php

namespace App\Http\Controllers\Shop;

use App\Models\Car;
use App\Models\ServiceType;
use App\Models\Visit;
use App\Support\Format;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends ShopController
{
    /** Bars on the visits chart: the selected month plus the preceding five. */
    private const WINDOW = 6;

    public function __invoke(Request $request): Response
    {
        $shopId = $request->user()->shop_id;

        $currentMonth = now()->startOfMonth();
        $anchor = $this->resolveAnchor($request->query('month'), $currentMonth);

        // The chart window ends at the selected (anchor) month
        $windowStart = $anchor->copy()->subMonths(self::WINDOW - 1)->startOfMonth();
        $windowEnd = $anchor->copy()->endOfMonth();

        // Bucketed in PHP so the query stays portable across SQLite and MariaDB
        $visitsByMonth = Visit::query()
            ->whereBetween('visited_at', [$windowStart, $windowEnd])
            ->get(['visited_at'])
            ->groupBy(fn (Visit $visit) => $visit->visited_at->format('Y-n'));

        $months = collect(range(self::WINDOW - 1, 0))->map(function (int $back) use ($anchor, $visitsByMonth) {
            $month = $anchor->copy()->subMonths($back);

            return [
                'label' => Format::monthName($month->month),
                'month' => $month->month,
                'year' => $month->year,
                'visits' => $visitsByMonth->get($month->format('Y-n'), collect())->count(),
            ];
        })->values();

        $windowConstraint = fn ($query) => $query
            ->where('visits.shop_id', $shopId)
            ->whereBetween('visits.visited_at', [$windowStart, $windowEnd]);

        $topServices = ServiceType::query()
            ->withCount(['visits' => $windowConstraint])
            ->withSum(['visits as revenue' => $windowConstraint], 'visit_services.price')
            ->get()
            ->filter(fn (ServiceType $service) => $service->visits_count > 0)
            ->sortByDesc('visits_count')
            ->take(4)
            ->map(fn (ServiceType $service) => [
                'label' => $service->displayName(),
                'count' => $service->visits_count,
                'revenue' => Format::price($service->revenue),
            ])
            ->values();

        // "Who to win back" is a present-tense action list, independent of the browsed month
        $lostCustomers = Car::query()
            ->whereHas('visits')
            ->whereDoesntHave('visits', fn ($query) => $query->where('visited_at', '>=', now()->subMonths(6)))
            ->with('customer', 'latestVisit')
            ->get()
            ->sortByDesc(fn (Car $car) => $car->latestVisit->visited_at)
            ->map(fn (Car $car) => [
                'owner' => $car->customer->displayName(),
                'ownerAr' => $car->customer->name,
                'car' => $car->displayLabel(),
                'carAr' => $car->labelAr(),
                'lastVisit' => Format::monthsAgo((int) $car->latestVisit->visited_at->diffInMonths(now())),
                'whatsapp' => $car->customer->whatsappNumber(),
            ])
            ->values();

        return Inertia::render('shop/analytics', [
            'shop' => $this->shopProps($request),
            'analytics' => [
                'months' => $months,
                'topServices' => $topServices,
                'lostCustomers' => $lostCustomers,
                'selected' => ['year' => $anchor->year, 'month' => $anchor->month],
                'max' => ['year' => $currentMonth->year, 'month' => $currentMonth->month],
                'monthNames' => collect(range(1, 12))->map(fn (int $m) => Format::monthName($m))->values(),
            ],
        ]);
    }

    /**
     * Parse the ?month=YYYY-M picker value into the anchor month, clamped so it
     * never runs into the future (no data yet) or absurdly far into the past.
     */
    private function resolveAnchor(?string $month, Carbon $currentMonth): Carbon
    {
        if (is_string($month) && preg_match('/^(\d{4})-(\d{1,2})$/', $month, $parts)) {
            $monthNumber = (int) $parts[2];

            if ($monthNumber >= 1 && $monthNumber <= 12) {
                $candidate = Carbon::create((int) $parts[1], $monthNumber, 1)->startOfMonth();

                if ($candidate->greaterThan($currentMonth)) {
                    return $currentMonth->copy();
                }

                if ($candidate->greaterThanOrEqualTo($currentMonth->copy()->subYears(20))) {
                    return $candidate;
                }
            }
        }

        return $currentMonth->copy();
    }
}
