<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => null, // broadcast
            'title' => fake()->sentence(3),
            'body' => fake()->paragraph(),
            'is_active' => true,
        ];
    }
}
