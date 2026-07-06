<?php

namespace Database\Factories;

use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceType>
 */
class ServiceTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => null, // global default chip
            'name' => fake()->unique()->randomElement(['تغيير زيت', 'فلتر زيت', 'فلتر هواء', 'فلتر مكيف', 'فحص فرامل', 'بطارية', 'دواليب', 'أخرى']),
            'sort_order' => 0,
        ];
    }
}
