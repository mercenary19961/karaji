<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use Database\Factories\PendingRegistrationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A customer's QR self-registration awaiting the shop owner's accept/reject.
 *
 * Per-shop (BelongsToShop) so a shop only ever sees its own queue; the public
 * intake sets shop_id via the `pendingRegistrations()` relation (no auth needed).
 *
 * @mixin IdeHelperPendingRegistration
 */
class PendingRegistration extends Model
{
    /** @use HasFactory<PendingRegistrationFactory> */
    use BelongsToShop, HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'plate',
        'label',
        'license_month',
    ];

    protected function casts(): array
    {
        return [
            'license_month' => 'integer',
        ];
    }
}
