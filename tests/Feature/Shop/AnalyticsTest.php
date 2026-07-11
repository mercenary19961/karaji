<?php

namespace Tests\Feature\Shop;

use App\Models\Shop;
use App\Models\User;
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
            ->where('analytics.months.5.year', $past->year));
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
}
