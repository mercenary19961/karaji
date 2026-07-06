<?php

namespace App\Http\Controllers\Shop;

use App\Models\Reminder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReminderController extends ShopController
{
    public function index(Request $request): Response
    {
        $reminders = Reminder::query()
            ->whereIn('status', ['pending', 'contacted'])
            ->whereDate('due_date', '<=', today())
            ->orderBy('due_date')
            ->with('car.customer')
            ->get();

        return Inertia::render('shop/reminders', [
            'shop' => $this->shopProps($request),
            'reminders' => $reminders->map(fn (Reminder $reminder) => [
                'id' => $reminder->id,
                'car' => $reminder->car->label ?? $reminder->car->plate,
                'owner' => $reminder->car->customer->name,
                'phone' => $reminder->car->customer->phone,
                'whatsapp' => $reminder->car->customer->whatsappNumber(),
                'due' => $reminder->label ?? $reminder->type,
                'overdueLabel' => $reminder->overdueLabel(),
                'contacted' => $reminder->status === 'contacted',
            ]),
        ]);
    }

    /**
     * Toggle "تم التواصل ✓" — tapping again undoes it (undo over confirm).
     */
    public function toggleContacted(Reminder $reminder): RedirectResponse
    {
        $reminder->status === 'contacted'
            ? $reminder->unmarkContacted()
            : $reminder->markContacted();

        return back();
    }
}
