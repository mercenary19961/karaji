<?php

namespace App\Models;

use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperSubscription
 */
class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    // Admin-managed, no tenancy scope: shops never query subscriptions directly.
    protected $fillable = [
        'shop_id',
        'plan',
        'status',
        'price_jod',
        'trial_ends_at',
        'renews_at',
    ];

    protected function casts(): array
    {
        return [
            'price_jod' => 'decimal:2',
            'trial_ends_at' => 'date',
            'renews_at' => 'date',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
