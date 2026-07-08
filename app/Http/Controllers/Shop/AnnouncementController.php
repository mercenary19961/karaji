<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Dismiss an announcement for the current shop only. Records a per-shop
     * dismissal (idempotent) so the dashboard banner stops showing it; the
     * announcement itself and its other recipients are untouched.
     */
    public function dismiss(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->dismissedBy()->syncWithoutDetaching([$request->user()->shop_id]);

        return back();
    }
}
