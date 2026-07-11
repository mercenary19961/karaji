<?php

namespace App\Http\Controllers\Shop;

use App\Models\Announcement;
use App\Models\Car;
use App\Models\Reminder;
use App\Models\Visit;
use App\Support\Format;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends ShopController
{
    public function __invoke(Request $request): Response
    {
        $dueQuery = Reminder::query()
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', today());

        $dueToday = (clone $dueQuery)
            ->orderBy('due_date')
            ->with('car.customer')
            ->limit(3)
            ->get();

        $announcements = Announcement::query()
            ->activeForShop($request->user()->shop_id)
            ->latest()
            ->get()
            ->map(fn (Announcement $a) => [
                'id' => $a->id,
                'title' => $a->displayTitle(),
                'body' => $a->displayBody(),
            ]);

        // Spec dashboard item: "customers you're losing" (no visit in 6+ months).
        $lostCustomers = Car::query()
            ->whereHas('visits')
            ->whereDoesntHave('visits', fn ($query) => $query->where('visited_at', '>=', now()->subMonths(6)))
            ->with('customer', 'latestVisit')
            ->get()
            ->sortByDesc(fn (Car $car) => $car->latestVisit->visited_at)
            ->take(3)
            ->map(fn (Car $car) => [
                'owner' => $car->customer->displayName(),
                'car' => $car->displayLabel(),
                'lastVisit' => Format::monthsAgo((int) $car->latestVisit->visited_at->diffInMonths(now())),
                'whatsapp' => $car->customer->whatsappNumber(),
            ])
            ->values();

        // The freshest activity — recent visitors/newcomers, not just overdue ones
        $recentVisits = Visit::query()
            ->latest('visited_at')
            ->with(['car.customer', 'services'])
            ->limit(4)
            ->get()
            ->map(fn (Visit $visit) => [
                'id' => $visit->id,
                'carId' => $visit->car_id,
                'car' => $visit->car->displayLabel(),
                'owner' => $visit->car->customer->displayName(),
                'date' => $visit->visited_at->format('d/m/Y'),
                'services' => $visit->services->map(fn ($service) => $service->displayName()),
            ]);

        return Inertia::render('shop/dashboard', [
            'shop' => $this->shopProps($request),
            'announcements' => $announcements,
            'recentVisits' => $recentVisits,
            'stats' => [
                'todayVisits' => Visit::query()->whereDate('visited_at', today())->count(),
                'dueCount' => $dueQuery->count(),
                // Revenue = sum of every service's charged price this month;
                // computed in PHP so it stays portable across SQLite and MariaDB.
                'monthRevenue' => number_format((float) Visit::query()
                    ->where('visited_at', '>=', now()->startOfMonth())
                    ->with('services')
                    ->get()
                    ->sum(fn (Visit $visit) => $visit->revenue())),
            ],
            'dueToday' => $dueToday->map(fn (Reminder $reminder) => [
                'car' => $reminder->car->displayLabel(),
                'owner' => $reminder->car->customer->displayName(),
                'due' => $reminder->displayLabel(),
                'overdueLabel' => $reminder->overdueLabel(),
            ]),
            'lostCustomers' => $lostCustomers,
        ]);
    }
}
