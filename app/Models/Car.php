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
}
