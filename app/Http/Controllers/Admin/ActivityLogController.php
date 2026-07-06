<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\ChangeLog\ChangeLogService;
use App\Services\ChangeLog\RevertResult;
use Illuminate\Http\RedirectResponse;

class ActivityLogController extends Controller
{
    public function __construct(private readonly ChangeLogService $changeLog) {}

    public function undo(ActivityLog $activityLog): RedirectResponse
    {
        $result = $this->changeLog->revert($activityLog);

        if ($result->ok) {
            return back()->with('success', 'Change undone');
        }

        return back()->with('error', match ($result->reason) {
            RevertResult::REASON_ALREADY_REVERTED => 'This change was already undone',
            RevertResult::REASON_CONFLICT => 'Can\'t undo — changed again since: '.implode(', ', $result->conflicts),
            RevertResult::REASON_SUBJECT_MISSING => 'Can\'t undo — the record no longer exists',
            default => 'This change can no longer be undone',
        });
    }
}
