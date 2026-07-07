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

class EntryTest extends TestCase
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

    private function car(string $plate, string $ownerName, string $phone): Car
    {
        $customer = Customer::factory()->create(['shop_id' => $this->shop->id, 'name' => $ownerName, 'phone' => $phone]);

        return Car::factory()->create(['shop_id' => $this->shop->id, 'customer_id' => $customer->id, 'plate' => $plate]);
    }

    public function test_the_entry_page_ships_the_shops_cars_as_a_search_index()
    {
        $car = $this->car('13-45210', 'معاذ الخطيب', '0796234567');

        $this->actingAs($this->user)->get('/shop/entry')->assertInertia(
            fn (Assert $page) => $page->component('shop/entry')
                ->has('cars', 1)
                ->where('cars.0.plate', '13-45210')
                ->where('cars.0.owner', 'معاذ الخطيب')
                ->where('cars.0.phone', '0796234567')
                ->where('cars.0.id', $car->id)
        );
    }

    public function test_the_index_is_scoped_to_the_shop()
    {
        $this->car('11-11111', 'زبون الكراج', '0790000001');

        // Another shop's car must never appear in this shop's index.
        $other = Shop::factory()->create();
        $otherCustomer = Customer::factory()->create(['shop_id' => $other->id]);
        Car::factory()->create(['shop_id' => $other->id, 'customer_id' => $otherCustomer->id, 'plate' => '99-99999']);

        $this->actingAs($this->user)->get('/shop/entry')->assertInertia(
            fn (Assert $page) => $page->has('cars', 1)->where('cars.0.plate', '11-11111')
        );
    }

    public function test_cars_are_ordered_most_recently_visited_first()
    {
        $old = $this->car('10-00001', 'قديم', '0790000010');
        $recent = $this->car('20-00002', 'جديد', '0790000020');

        Visit::factory()->create(['shop_id' => $this->shop->id, 'car_id' => $old->id, 'visited_at' => now()->subMonths(3)]);
        Visit::factory()->create(['shop_id' => $this->shop->id, 'car_id' => $recent->id, 'visited_at' => now()->subDay()]);

        $this->actingAs($this->user)->get('/shop/entry')->assertInertia(
            fn (Assert $page) => $page->where('cars.0.id', $recent->id)->where('cars.1.id', $old->id)
        );
    }

    public function test_the_new_customer_shortcut_opens_the_form_in_new_customer_mode()
    {
        $this->actingAs($this->user)->get('/shop/visits/new?new=1')->assertInertia(
            fn (Assert $page) => $page->component('shop/new-visit')->where('startNew', true)
        );

        // The plain form does not start in new-customer mode.
        $this->actingAs($this->user)->get('/shop/visits/new')->assertInertia(
            fn (Assert $page) => $page->where('startNew', false)
        );
    }
}
