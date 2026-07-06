<?php

namespace App\Http\Controllers\Shop;

use App\Models\Reminder;
use App\Models\Visit;
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

        return Inertia::render('shop/dashboard', [
            'shop' => $this->shopProps($request),
            'stats' => [
                'todayVisits' => Visit::query()->whereDate('visited_at', today())->count(),
                'dueCount' => $dueQuery->count(),
                'monthRevenue' => number_format((float) Visit::query()
                    ->where('visited_at', '>=', now()->startOfMonth())
                    ->sum('price')),
            ],
            'dueToday' => $dueToday->map(fn (Reminder $reminder) => [
                'car' => $reminder->car->label ?? $reminder->car->plate,
                'owner' => $reminder->car->customer->name,
                'due' => $reminder->label ?? $reminder->type,
                'overdueLabel' => $reminder->overdueLabel(),
            ]),
        ]);
    }
}
