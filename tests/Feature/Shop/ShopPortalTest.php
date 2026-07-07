<?php

namespace Tests\Feature\Shop;

use App\Models\Car;
use App\Models\Reminder;
use App\Models\Shop;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShopPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get('/shop')->assertRedirect('/login');
    }

    public function test_admins_are_redirected_to_the_admin_portal()
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/shop')->assertRedirect('/admin');
    }

    public function test_shop_users_can_view_all_shop_screens()
    {
        $shop = Shop::factory()->create();
        $car = Car::factory()->create(['shop_id' => $shop->id]);
        Visit::factory()->create(['shop_id' => $shop->id, 'car_id' => $car->id]);
        Reminder::factory()->create(['shop_id' => $shop->id, 'car_id' => $car->id, 'due_date' => today()->subDays(3)]);

        $this->actingAs(User::factory()->create(['shop_id' => $shop->id]));

        $screens = [
            '/shop' => 'shop/dashboard',
            '/shop/entry' => 'shop/entry',
            '/shop/visits/new' => 'shop/new-visit',
            "/shop/cars/{$car->id}" => 'shop/car',
            '/shop/reminders' => 'shop/reminders',
            '/shop/analytics' => 'shop/analytics',
        ];

        foreach ($screens as $uri => $component) {
            $this->get($uri)
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page->component($component)->has('shop.name'));
        }
    }

    public function test_shop_users_cannot_view_another_shops_car_page()
    {
        $foreignCar = Car::factory()->create();

        $shop = Shop::factory()->create();
        $this->actingAs(User::factory()->create(['shop_id' => $shop->id]));

        $this->get("/shop/cars/{$foreignCar->id}")->assertNotFound();
    }
}
