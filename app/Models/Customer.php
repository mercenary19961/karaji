<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperCustomer
 */
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use BelongsToShop, HasFactory;

    // shop_id intentionally not fillable: the BelongsToShop creating hook sets
    // it for shop users; admin flows must assign it explicitly.
    protected $fillable = [
        'name',
        'name_en',
        'phone',
        'notes',
    ];

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class);
    }

    /** Customer name in the current UI locale (English falls back to Arabic). */
    public function displayName(): string
    {
        return app()->getLocale() === 'en' && $this->name_en ? $this->name_en : $this->name;
    }

    /**
     * Local Jordanian mobile ("0795...") as a wa.me-ready number ("962795...").
     */
    public function whatsappNumber(): string
    {
        return '962'.ltrim($this->phone, '0');
    }
}
