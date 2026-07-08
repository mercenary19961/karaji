<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Change-log v2 entry (retab-stores port). Written ONLY through
 * App\Services\ChangeLog\ChangeLogService — no tenancy scope, admins read
 * it per shop explicitly.
 *
 * @mixin IdeHelperActivityLog
 */
class ActivityLog extends Model
{
    public const ACTION_CREATED = 'created';

    public const ACTION_UPDATED = 'updated';

    public const ACTION_DELETED = 'deleted';

    protected $fillable = [
        'user_id',
        'shop_id',
        'action',
        'subject_type',
        'subject_id',
        'old_data',
        'new_data',
        'label',
        'reverts_log_id',
        'reverted_at',
        'reverted_by',
    ];

    protected function casts(): array
    {
        return [
            'old_data' => 'array',
            'new_data' => 'array',
            'reverted_at' => 'datetime',
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

    /** The entry this one was created by reverting (mirror link — redo path). */
    public function revertsLog(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reverts_log_id');
    }

    public function isReverted(): bool
    {
        return $this->reverted_at !== null;
    }

    public function isRevert(): bool
    {
        return $this->reverts_log_id !== null;
    }
}
