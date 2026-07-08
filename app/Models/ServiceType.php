<?php

namespace App\Models;

use Database\Factories\ServiceTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperServiceType
 */
class ServiceType extends Model
{
    /** @use HasFactory<ServiceTypeFactory> */
    use HasFactory;

    // The seeded global chip the oil-reminder logic keys on (Arabic name is the
    // stable key; OIL_CHANGE_EN is only for display/labels).
    public const OIL_CHANGE = 'تغيير زيت';

    public const OIL_CHANGE_EN = 'Oil change';

    // No BelongsToShop: shop_id null means a global default chip, so the
    // hard tenancy scope would hide the defaults from every shop.
    protected $fillable = [
        'shop_id',
        'name',
        'name_en',
        'sort_order',
    ];

    /** Service name in the current UI locale (English falls back to Arabic). */
    public function displayName(): string
    {
        return app()->getLocale() === 'en' && $this->name_en ? $this->name_en : $this->name;
    }

    public function visits(): BelongsToMany
    {
        return $this->belongsToMany(Visit::class, 'visit_services');
    }

    /**
     * Chips a given shop can tap: the global defaults plus its own.
     */
    public function scopeAvailableToShop(Builder $query, int $shopId): Builder
    {
        return $query
            ->where(fn (Builder $q) => $q->whereNull('shop_id')->orWhere('shop_id', $shopId))
            ->orderBy('sort_order');
    }
}
