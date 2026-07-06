<?php

namespace Tests\Feature\Shop;

use App\Models\Car;
use App\Models\Customer;
use App\Models\Reminder;
use App\Models\ServiceType;
use App\Models\Shop;
use App\Models\User;
use App\Models\Visit;
use App\Services\Reminders\ReminderEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitFlowTest extends TestCase
{
    use RefreshDatabase;

    // POSTs without an oil_type default to mineral (see VisitController).
    private const MINERAL_KM = ReminderEngine::INTERVALS['mineral']['km'];

    private Shop $shop;

    private User $user;

    private ServiceType $oilChange;

    private ServiceType $battery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shop = Shop::factory()->create();
        $this->user = User::factory()->create(['shop_id' => $this->shop->id]);
        $this->oilChange = ServiceType::factory()->create(['name' => ServiceType::OIL_CHANGE]);
        $this->battery = ServiceType::factory()->create(['name' => 'بطارية']);
    }

    private function carInShop(): Car
    {
        return Car::factory()->create(['shop_id' => $this->shop->id]);
    }

    public function test_storing_a_visit_creates_services_and_schedules_the_oil_reminder()
    {
        $car = $this->carInShop();

        $response = $this->actingAs($this->user)->post('/shop/visits', [
            'car_id' => $car->id,
            'km' => 91300,
            'services' => [$this->oilChange->id, $this->battery->id],
            'oil_brand' => 'Mobil 5W-30',
            'price' => 28,
        ]);

        $visit = Visit::query()->sole();
        $response->assertRedirect("/shop/visits/new?car={$car->id}&saved={$visit->id}");

        $this->assertSame($this->shop->id, $visit->shop_id);
        $this->assertSame(91300, $visit->km);
        $this->assertSame(2, $visit->services()->count());

        $reminder = $car->pendingOilReminder()->sole();
        $this->assertSame(91300 + self::MINERAL_KM, $reminder->due_km);
        $this->assertNotNull($reminder->due_date);
    }

    public function test_a_second_oil_visit_updates_the_existing_reminder_instead_of_duplicating()
    {
        $car = $this->carInShop();

        $this->actingAs($this->user)->post('/shop/visits', ['car_id' => $car->id, 'km' => 80000, 'services' => [$this->oilChange->id]]);
        $this->actingAs($this->user)->post('/shop/visits', ['car_id' => $car->id, 'km' => 85000, 'services' => [$this->oilChange->id]]);

        $this->assertSame(1, $car->reminders()->count());
        $this->assertSame(85000 + self::MINERAL_KM, $car->pendingOilReminder()->sole()->due_km);
    }

    public function test_a_visit_without_oil_change_schedules_no_oil_reminder()
    {
        $car = $this->carInShop();

        $this->actingAs($this->user)->post('/shop/visits', [
            'car_id' => $car->id,
            'km' => 50000,
            'services' => [$this->battery->id],
        ]);

        $this->assertNull($car->pendingOilReminder()->first());
    }

    public function test_storing_with_a_new_customer_creates_customer_car_and_visit()
    {
        $this->actingAs($this->user)->post('/shop/visits', [
            'name' => 'أبو تجربة',
            'phone' => '0791234567',
            'plate' => '99-12345',
            'label' => 'هوندا سيفيك 2020',
            'km' => 50000,
            'services' => [$this->oilChange->id],
        ]);

        $customer = Customer::query()->sole();
        $this->assertSame($this->shop->id, $customer->shop_id);
        $this->assertSame('0791234567', $customer->phone);

        $car = $customer->cars()->sole();
        $this->assertSame('99-12345', $car->plate);
        $this->assertSame($this->shop->id, $car->shop_id);
        $this->assertSame(50000 + self::MINERAL_KM, $car->pendingOilReminder()->sole()->due_km);
    }

    public function test_a_known_phone_reuses_the_existing_customer()
    {
        $customer = Customer::factory()->create(['shop_id' => $this->shop->id, 'phone' => '0791234567']);

        $this->actingAs($this->user)->post('/shop/visits', [
            'name' => 'اسم مختلف',
            'phone' => '0791234567',
            'plate' => '88-54321',
            'km' => 60000,
            'services' => [$this->oilChange->id],
        ]);

        $this->assertSame(1, Customer::query()->count());
        $this->assertSame($customer->id, Car::query()->where('plate', '88-54321')->sole()->customer_id);
    }

    public function test_undo_deletes_the_visit_and_rederives_the_reminder()
    {
        $car = $this->carInShop();

        $this->actingAs($this->user)->post('/shop/visits', ['car_id' => $car->id, 'km' => 80000, 'services' => [$this->oilChange->id]]);
        $this->actingAs($this->user)->post('/shop/visits', ['car_id' => $car->id, 'km' => 85000, 'services' => [$this->oilChange->id]]);

        $latest = Visit::query()->where('km', 85000)->sole();
        $this->actingAs($this->user)->delete("/shop/visits/{$latest->id}");

        // The reminder falls back to the previous oil visit's schedule
        $this->assertSame(1, Visit::query()->count());
        $this->assertSame(80000 + self::MINERAL_KM, $car->pendingOilReminder()->sole()->due_km);

        $first = Visit::query()->sole();
        $this->actingAs($this->user)->delete("/shop/visits/{$first->id}");

        $this->assertSame(0, Visit::query()->count());
        $this->assertNull($car->pendingOilReminder()->first());
    }

    public function test_km_and_at_least_one_service_are_required()
    {
        $car = $this->carInShop();

        $this->actingAs($this->user)
            ->post('/shop/visits', ['car_id' => $car->id, 'services' => []])
            ->assertSessionHasErrors(['km', 'services']);
    }

    public function test_a_visit_cannot_be_stored_for_another_shops_car()
    {
        $foreignCar = Car::factory()->create();

        $this->actingAs($this->user)
            ->post('/shop/visits', ['car_id' => $foreignCar->id, 'km' => 50000, 'services' => [$this->oilChange->id]])
            ->assertNotFound();
    }

    public function test_search_finds_a_car_by_plate_fragment()
    {
        $car = Car::factory()->create(['shop_id' => $this->shop->id, 'plate' => '22-14853']);

        $this->actingAs($this->user)
            ->get('/shop/cars/search?q=148')
            ->assertRedirect("/shop/cars/{$car->id}");

        $this->actingAs($this->user)
            ->get('/shop/cars/search?q=148&to=visit')
            ->assertRedirect("/shop/visits/new?car={$car->id}");
    }

    public function test_search_finds_a_car_by_owner_phone_fragment()
    {
        $customer = Customer::factory()->create(['shop_id' => $this->shop->id, 'phone' => '0795123456']);
        $car = Car::factory()->create(['shop_id' => $this->shop->id, 'customer_id' => $customer->id]);

        $this->actingAs($this->user)
            ->get('/shop/cars/search?q=512345')
            ->assertRedirect("/shop/cars/{$car->id}");
    }

    public function test_search_never_matches_other_shops_cars()
    {
        Car::factory()->create(['plate' => '77-70707']);

        $this->actingAs($this->user)
            ->get('/shop/cars/search?q=70707')
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_reminder_contact_toggles_on_and_off()
    {
        $car = $this->carInShop();
        $reminder = Reminder::factory()->create(['shop_id' => $this->shop->id, 'car_id' => $car->id]);

        $this->actingAs($this->user)->post("/shop/reminders/{$reminder->id}/contacted");
        $this->assertSame('contacted', $reminder->fresh()->status);
        $this->assertNotNull($reminder->fresh()->contacted_at);

        $this->actingAs($this->user)->post("/shop/reminders/{$reminder->id}/contacted");
        $this->assertSame('pending', $reminder->fresh()->status);
        $this->assertNull($reminder->fresh()->contacted_at);
    }
}
