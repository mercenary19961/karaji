<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use Database\Factories\ServicePriceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A shop's default charge for a service type. Per-shop (BelongsToShop) so a shop
 * only ever sees and sets its own prices, and shop_id is auto-filled on create.
 *
 * @mixin IdeHelperServicePrice
 */
class ServicePrice extends Model
{
    /** @use HasFactory<ServicePriceFactory> */
    use BelongsToShop, HasFactory;

    // shop_id is never fillable on tenant models (auto-filled by BelongsToShop).
    protected $fillable = [
        'service_type_id',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }
}
