<?php

namespace Tests\Feature\Shop;

use App\Models\Car;
use App\Models\Customer;
use App\Models\Reminder;
use App\Models\ServicePrice;
use App\Models\ServiceType;
use App\Models\Shop;
use App\Models\User;
use App\Models\Visit;
use App\Services\Reminders\ReminderEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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
            'prices' => [$this->oilChange->id => 20, $this->battery->id => 45],
        ]);

        $visit = Visit::query()->sole();
        $response->assertRedirect("/shop/visits/new?car={$car->id}&saved={$visit->id}");

        $this->assertSame($this->shop->id, $visit->shop_id);
        $this->assertSame(91300, $visit->km);
        $this->assertSame(2, $visit->services()->count());

        // Per-service prices land on the pivot; the visit total is their sum
        $this->assertEquals(20, $visit->services()->where('service_type_id', $this->oilChange->id)->sole()->pivot->price);
        $this->assertEquals(65, $visit->load('services')->revenue());

        $reminder = $car->pendingOilReminder()->sole();
        $this->assertSame(91300 + self::MINERAL_KM, $reminder->due_km);
        $this->assertNotNull($reminder->due_date);
    }

    public function test_a_service_price_falls_back_to_the_shop_default_when_not_overridden()
    {
        ServicePrice::factory()->create(['shop_id' => $this->shop->id, 'service_type_id' => $this->oilChange->id, 'price' => 18]);
        $car = $this->carInShop();

        // No `prices` sent → the shop's saved default is used
        $this->actingAs($this->user)->post('/shop/visits', [
            'car_id' => $car->id,
            'km' => 40000,
            'services' => [$this->oilChange->id],
        ]);

        $this->assertEquals(18, Visit::query()->sole()->load('services')->revenue());
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

    public function test_labor_is_added_to_the_visit_revenue()
    {
        $car = $this->carInShop();

        $this->actingAs($this->user)->post('/shop/visits', [
            'car_id' => $car->id,
            'km' => 50000,
            'services' => [$this->oilChange->id],
            'prices' => [$this->oilChange->id => 20],
            'labor' => 8,
        ]);

        // 20 (oil-change parts) + 8 (labor)
        $this->assertEquals(28, Visit::query()->sole()->load('services')->revenue());
    }

    public function test_a_visit_saves_and_edits_notes()
    {
        $car = $this->carInShop();

        $this->actingAs($this->user)->post('/shop/visits', [
            'car_id' => $car->id, 'km' => 50000, 'services' => [$this->oilChange->id], 'notes' => 'الزبون بدو يرجع الأسبوع الجاي',
        ]);
        $visit = Visit::query()->sole();
        $this->assertSame('الزبون بدو يرجع الأسبوع الجاي', $visit->notes);

        $this->actingAs($this->user)->put("/shop/visits/{$visit->id}", [
            'km' => 50000, 'services' => [$this->oilChange->id], 'notes' => 'ملاحظة محدّثة',
        ]);
        $this->assertSame('ملاحظة محدّثة', $visit->refresh()->notes);
    }

    public function test_the_edit_visit_page_renders_the_current_services()
    {
        $car = $this->carInShop();
        $this->actingAs($this->user)->post('/shop/visits', [
            'car_id' => $car->id, 'km' => 80000, 'services' => [$this->oilChange->id], 'prices' => [$this->oilChange->id => 20],
        ]);
        $visit = Visit::query()->sole();

        $this->actingAs($this->user)
            ->get("/shop/visits/{$visit->id}/edit")
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/edit-visit')
                ->where('visit.id', $visit->id)
                ->has('visit.services', 1));
    }

    public function test_editing_a_visit_updates_km_services_prices_and_rederives_the_reminder()
    {
        $car = $this->carInShop();
        $this->actingAs($this->user)->post('/shop/visits', [
            'car_id' => $car->id, 'km' => 80000, 'services' => [$this->oilChange->id], 'prices' => [$this->oilChange->id => 20],
        ]);
        $visit = Visit::query()->sole();

        $this->actingAs($this->user)->put("/shop/visits/{$visit->id}", [
            'km' => 85000,
            'services' => [$this->oilChange->id, $this->battery->id],
            'prices' => [$this->oilChange->id => 25, $this->battery->id => 45],
        ])->assertRedirect("/shop/cars/{$car->id}");

        $visit->refresh();
        $this->assertSame(85000, $visit->km);
        $this->assertSame(2, $visit->services()->count());
        $this->assertEquals(70, $visit->load('services')->revenue());
        // The oil reminder is re-derived from the new km
        $this->assertSame(85000 + self::MINERAL_KM, $car->pendingOilReminder()->sole()->due_km);
    }

    public function test_a_shop_cannot_edit_another_shops_visit()
    {
        $foreignVisit = Visit::factory()->create();

        $this->actingAs($this->user)->get("/shop/visits/{$foreignVisit->id}/edit")->assertNotFound();
        $this->actingAs($this->user)
            ->put("/shop/visits/{$foreignVisit->id}", ['km' => 1000, 'services' => [$this->oilChange->id]])
            ->assertNotFound();
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
