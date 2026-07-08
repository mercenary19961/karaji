<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use Database\Factories\CarFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperCar
 */
class Car extends Model
{
    /** @use HasFactory<CarFactory> */
    use BelongsToShop, HasFactory;

    protected $fillable = [
        'customer_id',
        'plate',
        'label',
        'label_en',
        'license_month',
    ];

    protected function casts(): array
    {
        return [
            'license_month' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** Car label in the current UI locale, falling back to Arabic then plate. */
    public function displayLabel(): string
    {
        $label = app()->getLocale() === 'en' && $this->label_en ? $this->label_en : $this->label;

        return $label ?? $this->plate;
    }

    /** Always the Arabic label (or plate) — for the customer-facing WhatsApp text. */
    public function labelAr(): string
    {
        return $this->label ?? $this->plate;
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function latestVisit(): HasOne
    {
        return $this->hasOne(Visit::class)->latestOfMany('visited_at');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    public function pendingOilReminder(): HasOne
    {
        return $this->hasOne(Reminder::class)->where('type', 'oil')->where('status', 'pending');
    }

    /**
     * The most recent visit that included an oil change — the anchor for the
     * next-due estimate and the "same as last time" form defaults.
     */
    public function latestOilVisit(): ?Visit
    {
        return $this->visits()
            ->whereHas('services', fn ($query) => $query->where('name', ServiceType::OIL_CHANGE))
            ->latest('visited_at')
            ->first();
    }

    /**
     * License month rendered as "11/2026" — the next occurrence of that month.
     */
    public function licenseMonthLabel(): ?string
    {
        if ($this->license_month === null) {
            return null;
        }

        $year = $this->license_month >= now()->month ? now()->year : now()->year + 1;

        return "{$this->license_month}/{$year}";
    }
}
