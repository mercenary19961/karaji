<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Lean version of hardrock's ActivityLogService: every admin edit records
 * who/what/when plus the before-state that powers Undo.
 */
class AdminActivity
{
    public static function log(int $shopId, string $action, ?Model $subject = null, array $before = [], array $after = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'shop_id' => $shopId,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'change_set' => $before === [] && $after === [] ? null : ['before' => $before, 'after' => $after],
        ]);
    }
}
