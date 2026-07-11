<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use Database\Factories\VisitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperVisit
 */
class Visit extends Model
{
    /** @use HasFactory<VisitFactory> */
    use BelongsToShop, HasFactory;

    protected $fillable = [
        'car_id',
        'km',
        'labor',
        'oil_brand',
        'oil_type',
        'notes',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'km' => 'integer',
            'labor' => 'decimal:2',
            'visited_at' => 'datetime',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(ServiceType::class, 'visit_services')->withPivot('price');
    }

    /**
     * The visit total = sum of its per-service (parts) prices + the labor charge.
     * Null when nothing was priced (so callers can hide the line instead of 0).
     * Assumes `services` is loaded.
     */
    public function revenue(): ?float
    {
        $priced = $this->services->pluck('pivot.price')->filter(fn ($price) => $price !== null);

        if ($priced->isEmpty() && $this->labor === null) {
            return null;
        }

        return (float) $priced->sum() + (float) $this->labor;
    }
}
