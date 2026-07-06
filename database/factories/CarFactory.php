<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Customer;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Car>
 */
class CarFactory extends Factory
{
    public function definition(): array
    {
        return [
            // shop_id resolves before the closure below, keeping the car and
            // its owner in the same shop.
            'shop_id' => Shop::factory(),
            'customer_id' => fn (array $attributes) => Customer::factory()->create(['shop_id' => $attributes['shop_id']])->id,
            'plate' => fake()->unique()->numerify('##-#####'),
            'label' => fake()->randomElement(['كيا سبورتاج', 'هيونداي إلنترا', 'تويوتا كامري', 'ميتسوبيشي لانسر', 'مرسيدس E200', 'هوندا سيفيك']).' '.fake()->numberBetween(2012, 2024),
            'license_month' => fake()->optional()->numberBetween(1, 12),
        ];
    }
}
