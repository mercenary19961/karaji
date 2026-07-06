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
        'price',
        'oil_brand',
        'notes',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'km' => 'integer',
            'price' => 'decimal:2',
            'visited_at' => 'datetime',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(ServiceType::class, 'visit_services');
    }
}
