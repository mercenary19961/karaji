<?php

namespace App\Models\Concerns;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Multi-tenancy for shop-owned models (single DB, shop_id column).
 *
 * - Shop users (shop_id set) are hard-scoped to their shop's rows and get
 *   shop_id auto-filled on create.
 * - Admins (shop_id null) and unauthenticated contexts (console, queue —
 *   e.g. the reminders engine) see everything; admin writes must set
 *   shop_id explicitly.
 *
 * Bypass explicitly with Model::withoutGlobalScope('shop') when an admin
 * flow needs it spelled out.
 */
trait BelongsToShop
{
    protected static function bootBelongsToShop(): void
    {
        static::addGlobalScope('shop', function (Builder $query) {
            $shopId = Auth::user()?->shop_id;

            if ($shopId !== null) {
                $query->where($query->getModel()->getTable().'.shop_id', $shopId);
            }
        });

        static::creating(function (Model $model) {
            if ($model->getAttribute('shop_id') === null) {
                $model->setAttribute('shop_id', Auth::user()?->shop_id);
            }
        });
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
