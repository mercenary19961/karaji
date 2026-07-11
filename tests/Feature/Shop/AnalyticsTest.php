<?php

namespace Tests\Feature\Shop;

use App\Models\Car;
use App\Models\ServiceType;
use App\Models\Shop;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsShopUser(): void
    {
        $shop = Shop::factory()->create();
        $this->actingAs(User::factory()->create(['shop_id' => $shop->id]));
    }

    public function test_it_defaults_to_the_current_month()
    {
        $this->actingAsShopUser();

        $this->get('/shop/analytics')->assertInertia(fn (Assert $page) => $page
            ->component('shop/analytics')
            ->where('analytics.selected.year', now()->year)
            ->where('analytics.selected.month', now()->month)
            ->has('analytics.months', 6)
            ->has('analytics.monthNames', 12));
    }

    public function test_a_picked_month_becomes_the_selected_window_end()
    {
        $this->actingAsShopUser();

        $past = now()->subMonths(2)->startOfMonth();

        $this->get("/shop/analytics?month={$past->year}-{$past->month}")->assertInertia(fn (Assert $page) => $page
            ->where('analytics.selected.year', $past->year)
            ->where('analytics.selected.month', $past->month)
            ->has('analytics.months', 6)
            ->where('analytics.months.5.year', $past->year)
            ->where('analytics.months.5.month', $past->month));
    }

    public function test_a_future_month_is_clamped_to_the_current_month()
    {
        $this->actingAsShopUser();

        $future = now()->addYears(2);

        $this->get("/shop/analytics?month={$future->year}-{$future->month}")->assertInertia(fn (Assert $page) => $page
            ->where('analytics.selected.year', now()->year)
            ->where('analytics.selected.month', now()->month));
    }

    public function test_a_malformed_month_falls_back_to_the_current_month()
    {
        $this->actingAsShopUser();

        $this->get('/shop/analytics?month=not-a-month')->assertInertia(fn (Assert $page) => $page
            ->where('analytics.selected.year', now()->year)
            ->where('analytics.selected.month', now()->month));
    }

    public function test_top_services_include_the_summed_revenue()
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create(['shop_id' => $shop->id, 'locale' => 'en']);
        $oil = ServiceType::factory()->create(['name' => ServiceType::OIL_CHANGE]);
        $car = Car::factory()->create(['shop_id' => $shop->id]);

        foreach ([20, 22] as $price) {
            Visit::factory()
                ->create(['shop_id' => $shop->id, 'car_id' => $car->id, 'visited_at' => now()])
                ->services()->attach([$oil->id => ['price' => $price]]);
        }

        $this->actingAs($user)->get('/shop/analytics')->assertInertia(fn (Assert $page) => $page
            ->where('analytics.topServices.0.count', 2)
            ->where('analytics.topServices.0.revenue', '42 JOD'));
    }
}
