<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenancyTest extends TestCase
{
    use RefreshDatabase;

    private function twoShopsWithData(): array
    {
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();

        Car::factory()->create(['shop_id' => $shopA->id, 'plate' => '11-11111']);
        Car::factory()->create(['shop_id' => $shopB->id, 'plate' => '22-22222']);

        return [$shopA, $shopB];
    }

    public function test_shop_users_only_see_their_own_shops_records()
    {
        [$shopA] = $this->twoShopsWithData();

        $this->actingAs(User::factory()->create(['shop_id' => $shopA->id]));

        $this->assertSame(['11-11111'], Car::query()->pluck('plate')->all());
        $this->assertSame(1, Customer::query()->count());
    }

    public function test_admins_see_records_across_all_shops()
    {
        $this->twoShopsWithData();

        $this->actingAs(User::factory()->admin()->create());

        $this->assertSame(2, Car::query()->count());
        $this->assertSame(2, Customer::query()->count());
    }

    public function test_unauthenticated_contexts_are_unscoped()
    {
        // Console/queue (e.g. the reminders engine) must see every shop
        $this->twoShopsWithData();

        $this->assertSame(2, Car::query()->count());
    }

    public function test_creating_records_as_shop_user_autofills_shop_id()
    {
        $shop = Shop::factory()->create();
        $this->actingAs(User::factory()->create(['shop_id' => $shop->id]));

        $customer = Customer::create(['name' => 'زبون جديد', 'phone' => '0791111111']);

        $this->assertSame($shop->id, $customer->shop_id);
    }

    public function test_shop_users_cannot_reach_other_shops_records_by_id()
    {
        [$shopA, $shopB] = $this->twoShopsWithData();
        $foreignCar = Car::withoutGlobalScope('shop')->where('shop_id', $shopB->id)->first();

        $this->actingAs(User::factory()->create(['shop_id' => $shopA->id]));

        $this->assertNull(Car::find($foreignCar->id));
    }

    public function test_demo_seeder_populates_the_demo_shop()
    {
        $this->seed();

        $shop = Shop::query()->where('name', 'كراج أبو رامز')->first();

        $this->assertNotNull($shop);
        $this->assertSame(5, $shop->reminders()->where('status', 'pending')->count());
        $this->assertNotNull(Car::query()->where('plate', '22-14853')->first());
        $this->assertSame(4, Car::query()->where('plate', '22-14853')->first()->visits()->count());
        $this->assertTrue(User::query()->where('email', 'shop@example.com')->value('shop_id') === $shop->id);
    }
}
