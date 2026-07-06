<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Shop;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'car_id' => fn (array $attributes) => Car::factory()->create(['shop_id' => $attributes['shop_id']])->id,
            'km' => fake()->numberBetween(20000, 250000),
            'price' => fake()->optional()->randomFloat(2, 15, 120),
            'oil_brand' => fake()->optional()->randomElement(['Mobil 5W-30', 'Castrol 5W-40', 'Total 10W-40', 'Shell 5W-30']),
            'oil_type' => fake()->randomElement(['mineral', 'synthetic']),
            'visited_at' => fake()->dateTimeBetween('-1 year'),
        ];
    }
}
