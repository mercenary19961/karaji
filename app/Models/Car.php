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

    // v1 defaults for the next-oil-change estimate; becomes per-oil-type
    // (mineral/synthetic) with the full reminders engine.
    public const OIL_INTERVAL_KM = 5000;

    public const OIL_INTERVAL_MONTHS = 6;

    protected $fillable = [
        'customer_id',
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
     * Single write path for the car's pending oil reminder: derive it from the
     * latest oil-change visit (called after storing AND after undoing a visit,
     * so the reminder always matches reality).
     */
    public function syncOilReminder(): void
    {
        $latestOilVisit = $this->visits()
            ->whereHas('services', fn ($query) => $query->where('name', ServiceType::OIL_CHANGE))
            ->latest('visited_at')
            ->first();

        $pending = $this->pendingOilReminder()->first();

        if ($latestOilVisit === null) {
            $pending?->delete();

            return;
        }

        $attributes = [
            'visit_id' => $latestOilVisit->id,
            'label' => ServiceType::OIL_CHANGE,
            'due_km' => $latestOilVisit->km + self::OIL_INTERVAL_KM,
            'due_date' => $latestOilVisit->visited_at->addMonths(self::OIL_INTERVAL_MONTHS)->toDateString(),
        ];

        if ($pending !== null) {
            $pending->update($attributes);

            return;
        }

        $reminder = $this->reminders()->make([...$attributes, 'type' => 'oil', 'status' => 'pending']);
        $reminder->shop_id = $this->shop_id;
        $reminder->save();
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
