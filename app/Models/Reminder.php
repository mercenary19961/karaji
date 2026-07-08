<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use App\Support\Format;
use Database\Factories\ReminderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperReminder
 */
class Reminder extends Model
{
    /** @use HasFactory<ReminderFactory> */
    use BelongsToShop, HasFactory;

    protected $fillable = [
        'car_id',
        'visit_id',
        'type',
        'label',
        'label_en',
        'due_km',
        'due_date',
        'status',
        'contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'due_km' => 'integer',
            'due_date' => 'date',
            'contacted_at' => 'datetime',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /** The "what's due" text in the current UI locale (English falls back). */
    public function displayLabel(): string
    {
        return app()->getLocale() === 'en' && $this->label_en ? $this->label_en : $this->labelAr();
    }

    /** Always the Arabic due text — for the customer-facing WhatsApp message. */
    public function labelAr(): string
    {
        return $this->label ?? $this->type;
    }

    public function overdueLabel(): string
    {
        $days = $this->due_date === null ? 0 : (int) $this->due_date->startOfDay()->diffInDays(today(), false);

        return Format::overdueDays($days);
    }

    public function markContacted(): void
    {
        if ($this->status === 'contacted') {
            return;
        }

        $this->update(['status' => 'contacted', 'contacted_at' => now()]);
    }

    public function unmarkContacted(): void
    {
        if ($this->status !== 'contacted') {
            return;
        }

        $this->update(['status' => 'pending', 'contacted_at' => null]);
    }
}
