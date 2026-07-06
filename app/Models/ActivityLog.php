<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperActivityLog
 */
class ActivityLog extends Model
{
    // Written by the (to-be-ported) ActivityLogService only — no factory,
    // no tenancy scope, admins read it per shop explicitly.
    protected $fillable = [
        'user_id',
        'shop_id',
        'action',
        'subject_type',
        'subject_id',
        'change_set',
        'undone_at',
    ];

    protected function casts(): array
    {
        return [
            'change_set' => 'array',
            'undone_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function isUndoable(): bool
    {
        return $this->undone_at === null
            && filled($this->change_set['before'] ?? null)
            && $this->subject !== null;
    }

    /**
     * Restore the subject's before-state. One-shot: a log entry can only be
     * undone once (undone_at marks it spent).
     */
    public function undo(): bool
    {
        if (! $this->isUndoable()) {
            return false;
        }

        $this->subject->update($this->change_set['before']);
        $this->update(['undone_at' => now()]);

        return true;
    }
}
