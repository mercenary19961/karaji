<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\Suggestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Suggestion>
 */
class SuggestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'body' => fake()->sentence(10),
            'status' => Suggestion::STATUS_OPEN,
        ];
    }
}
