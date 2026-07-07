<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use Database\Factories\SuggestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Shop -> admin suggestion. Uses BelongsToShop: a shop user creating one gets
 * shop_id auto-filled and only sees their own; admins (shop_id null) see all.
 *
 * @mixin IdeHelperSuggestion
 */
class Suggestion extends Model
{
    /** @use HasFactory<SuggestionFactory> */
    use BelongsToShop, HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_REVIEWED = 'reviewed';

    protected $fillable = [
        'body',
        'status',
    ];
}
