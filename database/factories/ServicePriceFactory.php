<?php

namespace Database\Factories;

use App\Models\ServicePrice;
use App\Models\ServiceType;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServicePrice>
 */
class ServicePriceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'service_type_id' => ServiceType::factory(),
            'price' => fake()->randomElement([5, 7, 10, 15, 20, 25, 45]),
        ];
    }
}
