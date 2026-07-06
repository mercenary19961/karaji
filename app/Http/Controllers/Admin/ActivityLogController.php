<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;

class ActivityLogController extends Controller
{
    public function undo(ActivityLog $activityLog): RedirectResponse
    {
        return $activityLog->undo()
            ? back()->with('success', 'Change undone')
            : back()->with('error', 'This change can no longer be undone');
    }
}
