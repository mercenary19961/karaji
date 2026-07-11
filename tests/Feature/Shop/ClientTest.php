<?php

namespace Tests\Feature\Shop;

use App\Models\Car;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shop = Shop::factory()->create();
        $this->user = User::factory()->create(['shop_id' => $this->shop->id]);
    }

    public function test_the_clients_page_lists_only_this_shops_cars()
    {
        Car::factory()->count(3)->create(['shop_id' => $this->shop->id]);
        Car::factory()->create(); // another shop's car — must not appear

        $this->actingAs($this->user)
            ->get('/shop/clients')
            ->assertInertia(fn (Assert $page) => $page->component('shop/clients')->has('clients', 3));
    }

    public function test_the_edit_page_renders_the_current_details()
    {
        $customer = Customer::factory()->create(['shop_id' => $this->shop->id, 'name' => 'أبو تجربة', 'phone' => '0790000000']);
        $car = Car::factory()->create(['shop_id' => $this->shop->id, 'customer_id' => $customer->id, 'plate' => '11-11111']);

        $this->actingAs($this->user)
            ->get("/shop/cars/{$car->id}/edit")
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/edit-client')
                ->where('client.name', 'أبو تجربة')
                ->where('client.plate', '11-11111')
                ->has('licenseMonths', 12));
    }

    public function test_updating_changes_the_customer_and_car()
    {
        $customer = Customer::factory()->create(['shop_id' => $this->shop->id, 'name' => 'قديم', 'phone' => '0790000000']);
        $car = Car::factory()->create(['shop_id' => $this->shop->id, 'customer_id' => $customer->id, 'plate' => '11-11111']);

        $this->actingAs($this->user)->put("/shop/cars/{$car->id}", [
            'name' => 'جديد',
            'phone' => '0791111111',
            'plate' => '22-22222',
            'label' => 'كيا سيراتو',
            'license_month' => 5,
        ])->assertRedirect("/shop/cars/{$car->id}");

        $car->refresh();
        $this->assertSame('22-22222', $car->plate);
        $this->assertSame('كيا سيراتو', $car->label);
        $this->assertSame(5, $car->license_month);
        $this->assertSame('جديد', $car->customer->name);
        $this->assertSame('0791111111', $car->customer->phone);
    }

    public function test_keeping_the_same_plate_and_phone_is_allowed()
    {
        $customer = Customer::factory()->create(['shop_id' => $this->shop->id, 'phone' => '0790000000']);
        $car = Car::factory()->create(['shop_id' => $this->shop->id, 'customer_id' => $customer->id, 'plate' => '11-11111']);

        $this->actingAs($this->user)->put("/shop/cars/{$car->id}", [
            'name' => 'اسم جديد',
            'phone' => '0790000000', // unchanged — must not trip the unique rule
            'plate' => '11-11111',   // unchanged
        ])->assertSessionHasNoErrors();
    }

    public function test_a_duplicate_plate_or_phone_is_rejected()
    {
        Car::factory()->create(['shop_id' => $this->shop->id, 'plate' => '55-55555']);
        Customer::factory()->create(['shop_id' => $this->shop->id, 'phone' => '0799999999']);

        $car = Car::factory()->create(['shop_id' => $this->shop->id]);

        $this->actingAs($this->user)->put("/shop/cars/{$car->id}", [
            'name' => 'اسم',
            'phone' => '0799999999',
            'plate' => '55-55555',
        ])->assertSessionHasErrors(['phone', 'plate']);
    }

    public function test_a_shop_cannot_edit_another_shops_car()
    {
        $foreignCar = Car::factory()->create();

        $this->actingAs($this->user)->get("/shop/cars/{$foreignCar->id}/edit")->assertNotFound();
        $this->actingAs($this->user)->put("/shop/cars/{$foreignCar->id}", [
            'name' => 'x', 'phone' => '0791234567', 'plate' => '99-00000',
        ])->assertNotFound();
    }

    public function test_the_dashboard_surfaces_recent_visits()
    {
        $car = Car::factory()->create(['shop_id' => $this->shop->id]);
        Visit::factory()->create(['shop_id' => $this->shop->id, 'car_id' => $car->id, 'visited_at' => now()]);

        $this->actingAs($this->user)
            ->get('/shop')
            ->assertInertia(fn (Assert $page) => $page->component('shop/dashboard')->has('recentVisits', 1));
    }
}
