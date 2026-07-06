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

    // The seeded global chip the oil-reminder logic keys on
    public const OIL_CHANGE = 'تغيير زيت';

    // No BelongsToShop: shop_id null means a global default chip, so the
    // hard tenancy scope would hide the defaults from every shop.
    protected $fillable = [
        'shop_id',
        'name',
        'sort_order',
    ];

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
