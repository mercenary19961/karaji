<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Reminder;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reminder>
 */
class ReminderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'car_id' => fn (array $attributes) => Car::factory()->create(['shop_id' => $attributes['shop_id']])->id,
            'type' => 'oil',
            'label' => 'تغيير زيت',
            'due_km' => fake()->numberBetween(30000, 250000),
            'due_date' => fake()->dateTimeBetween('-1 month', '+3 months'),
            'status' => 'pending',
        ];
    }
}
