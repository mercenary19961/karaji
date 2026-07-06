<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'plan' => 'basic',
            'status' => 'active',
            'price_jod' => 15,
            'renews_at' => now()->addMonth()->toDateString(),
        ];
    }

    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'trial',
            'price_jod' => null,
            'renews_at' => null,
            'trial_ends_at' => now()->addMonth()->toDateString(),
        ]);
    }
}
