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
        'changes',
        'undone_at',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
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
}
