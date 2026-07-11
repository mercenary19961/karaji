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
        'oil_brand',
        'oil_type',
        'notes',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'km' => 'integer',
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
     * The visit total = sum of its per-service prices. Null when none of the
     * services were priced (so callers can hide the line instead of showing 0).
     * Assumes `services` is loaded.
     */
    public function revenue(): ?float
    {
        $priced = $this->services->pluck('pivot.price')->filter(fn ($price) => $price !== null);

        return $priced->isEmpty() ? null : (float) $priced->sum();
    }
}
