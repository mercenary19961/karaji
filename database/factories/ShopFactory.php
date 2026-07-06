<?php

namespace Database\Factories;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'كراج '.fake()->firstNameMale(),
            'area' => fake()->randomElement(['ماركا', 'البيادر', 'صويلح', 'طبربور', 'وادي صقرة', 'مرج الحمام', 'الوحدات', 'الهاشمي الشمالي']),
            'phone' => '06'.fake()->numerify('#######'),
            'default_daily_km' => 40,
        ];
    }
}
