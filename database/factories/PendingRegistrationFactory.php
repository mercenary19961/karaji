<?php

namespace Database\Factories;

use App\Models\PendingRegistration;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PendingRegistration>
 */
class PendingRegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'name' => fake()->name(),
            'phone' => '07'.fake()->numerify('########'),
            'plate' => fake()->numerify('##-#####'),
            'label' => fake()->optional()->randomElement(['تويوتا كورولا 2018', 'هيونداي إلنترا 2020', 'كيا سيراتو 2019']),
        ];
    }
}
