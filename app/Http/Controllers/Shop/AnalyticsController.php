<?php

namespace App\Http\Controllers\Shop;

use App\Models\Car;
use App\Models\ServiceType;
use App\Models\Visit;
use App\Support\Format;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends ShopController
{
    public function __invoke(Request $request): Response
    {
        $shopId = $request->user()->shop_id;
        $windowStart = now()->subMonths(5)->startOfMonth();

        // Bucketed in PHP so the query stays portable across SQLite and MariaDB
        $visitsByMonth = Visit::query()
            ->where('visited_at', '>=', $windowStart)
            ->get(['visited_at'])
            ->groupBy(fn (Visit $visit) => $visit->visited_at->format('Y-n'));

        $months = collect(range(5, 0))->map(function (int $back) use ($visitsByMonth) {
            $month = now()->subMonths($back);

            return [
                'label' => Format::monthName($month->month),
                'year' => $month->year,
                'visits' => $visitsByMonth->get($month->format('Y-n'), collect())->count(),
            ];
        })->values();

        $topServices = ServiceType::query()
            ->withCount(['visits' => fn ($query) => $query
                ->where('visits.shop_id', $shopId)
                ->where('visits.visited_at', '>=', $windowStart)])
            ->get()
            ->filter(fn (ServiceType $service) => $service->visits_count > 0)
            ->sortByDesc('visits_count')
            ->take(4)
            ->map(fn (ServiceType $service) => ['label' => $service->name, 'count' => $service->visits_count])
            ->values();

        $lostCustomers = Car::query()
            ->whereHas('visits')
            ->whereDoesntHave('visits', fn ($query) => $query->where('visited_at', '>=', now()->subMonths(6)))
            ->with('customer', 'latestVisit')
            ->get()
            ->sortByDesc(fn (Car $car) => $car->latestVisit->visited_at)
            ->map(fn (Car $car) => [
                'owner' => $car->customer->name,
                'car' => $car->label ?? $car->plate,
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
            ],
        ]);
    }
}
