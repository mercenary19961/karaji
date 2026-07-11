<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Customer;
use App\Models\PendingRegistration;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class JoinTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_public_join_page_renders_for_a_valid_token()
    {
        $shop = Shop::factory()->create();

        $this->get("/join/{$shop->public_token}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('public/join')->where('shopName', $shop->name));
    }

    public function test_an_unknown_token_is_not_found()
    {
        $this->get('/join/does-not-exist')->assertNotFound();
    }

    public function test_a_submission_queues_as_pending_when_auto_accept_is_off()
    {
        $shop = Shop::factory()->create(['auto_accept_registrations' => false]);

        $this->post("/join/{$shop->public_token}", [
            'name' => 'زبون جديد',
            'phone' => '0791234567',
            'plate' => '99-11111',
            'label' => 'هوندا سيفيك 2020',
        ]);

        $this->assertSame(1, PendingRegistration::query()->where('shop_id', $shop->id)->count());
        $this->assertSame(0, Customer::query()->count());
        $this->assertSame(0, Car::query()->count());
    }

    public function test_a_submission_creates_the_customer_immediately_when_auto_accept_is_on()
    {
        $shop = Shop::factory()->create(['auto_accept_registrations' => true]);

        $this->post("/join/{$shop->public_token}", [
            'name' => 'زبون تلقائي',
            'phone' => '0791234567',
            'plate' => '99-22222',
        ]);

        $this->assertSame(0, PendingRegistration::query()->count());

        $customer = Customer::query()->sole();
        $this->assertSame($shop->id, $customer->shop_id);
        $this->assertSame('0791234567', $customer->phone);

        $car = Car::query()->sole();
        $this->assertSame($shop->id, $car->shop_id);
        $this->assertSame('99-22222', $car->plate);
    }

    public function test_name_phone_and_plate_are_required()
    {
        $shop = Shop::factory()->create();

        $this->post("/join/{$shop->public_token}", [])
            ->assertSessionHasErrors(['name', 'phone', 'plate']);
    }
}
