<?php

namespace App\Models;

use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperAnnouncement
 */
class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasFactory;

    // Admin-authored; shop_id null = broadcast to all shops, so no tenancy scope.
    protected $fillable = [
        'shop_id',
        'title',
        'body',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /** Shops that have dismissed this announcement (hidden for them only). */
    public function dismissedBy(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'announcement_dismissals')->withTimestamps();
    }

    /**
     * Announcements a shop should see right now: active, within any date
     * window, either a broadcast (shop_id null) or targeted at this shop, and
     * not dismissed by this shop.
     */
    public function scopeActiveForShop(Builder $query, int $shopId): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('shop_id')->orWhere('shop_id', $shopId))
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', today()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))
            ->whereDoesntHave('dismissedBy', fn (Builder $q) => $q->where('shops.id', $shopId));
    }

    public function isBroadcast(): bool
    {
        return $this->shop_id === null;
    }
}
