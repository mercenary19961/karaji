<?php

namespace App\Models;

use Database\Factories\ShopFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * @mixin IdeHelperShop
 */
class Shop extends Model
{
    /** @use HasFactory<ShopFactory> */
    use HasFactory;

    // auto_accept_registrations is a shop-owner setting; public_token is
    // generated (not fillable) so it can't be set from a form.
    protected $fillable = [
        'name',
        'name_en',
        'area',
        'area_en',
        'phone',
        'default_daily_km',
        'auto_accept_registrations',
    ];

    protected function casts(): array
    {
        return [
            'default_daily_km' => 'integer',
            'auto_accept_registrations' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Shop $shop) {
            if (empty($shop->public_token)) {
                $shop->public_token = Str::random(40);
            }
        });
    }

    /**
     * Create (or reuse) a customer + car for THIS shop from raw intake, works in
     * any context (no auth) — used by both QR auto-accept and the accept flow.
     * Idempotent on the shop's unique (phone) customer and (plate) car.
     */
    public function registerCar(string $name, string $phone, string $plate, ?string $label = null): Car
    {
        $customer = Customer::withoutGlobalScope('shop')
            ->where('shop_id', $this->id)
            ->where('phone', $phone)
            ->first();

        if ($customer === null) {
            $customer = new Customer(['name' => $name, 'phone' => $phone]);
            $customer->shop_id = $this->id;
            $customer->save();
        }

        $car = Car::withoutGlobalScope('shop')
            ->where('shop_id', $this->id)
            ->where('plate', $plate)
            ->first();

        if ($car === null) {
            $car = new Car(['customer_id' => $customer->id, 'plate' => $plate, 'label' => $label]);
            $car->shop_id = $this->id;
            $car->save();
        }

        return $car;
    }

    /** Shop name in the current UI locale (English falls back to Arabic). */
    public function displayName(): string
    {
        return app()->getLocale() === 'en' && $this->name_en ? $this->name_en : $this->name;
    }

    /** Shop area in the current UI locale (English falls back to Arabic). */
    public function displayArea(): ?string
    {
        return app()->getLocale() === 'en' && $this->area_en ? $this->area_en : $this->area;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function latestVisit(): HasOne
    {
        return $this->hasOne(Visit::class)->latestOfMany('visited_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(Suggestion::class);
    }

    public function pendingRegistrations(): HasMany
    {
        return $this->hasMany(PendingRegistration::class);
    }
}
