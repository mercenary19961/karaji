<?php

namespace Tests\Feature\Shop;

use App\Models\Car;
use App\Models\Customer;
use App\Models\PendingRegistration;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RegistrationTest extends TestCase
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

    public function test_the_registrations_page_shows_the_join_url_and_pending_queue()
    {
        PendingRegistration::factory()->create(['shop_id' => $this->shop->id]);

        $this->actingAs($this->user)
            ->get('/shop/registrations')
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/registrations')
                ->where('joinUrl', route('join.show', $this->shop->public_token))
                ->has('pending', 1));
    }

    public function test_accepting_creates_the_customer_and_car_and_clears_the_pending()
    {
        $pending = PendingRegistration::factory()->create([
            'shop_id' => $this->shop->id,
            'name' => 'زبون QR',
            'phone' => '0791234567',
            'plate' => '30-45678',
            'label' => 'مازدا 3',
        ]);

        $response = $this->actingAs($this->user)->post("/shop/registrations/{$pending->id}/accept");

        $this->assertSame(0, PendingRegistration::query()->count());
        $this->assertSame('0791234567', Customer::query()->sole()->phone);

        // Accept lands the owner on a new visit for the just-added car
        $car = Car::query()->sole();
        $this->assertSame('30-45678', $car->plate);
        $response->assertRedirect("/shop/visits/new?car={$car->id}");
    }

    public function test_rejecting_deletes_the_pending_without_creating_anything()
    {
        $pending = PendingRegistration::factory()->create(['shop_id' => $this->shop->id]);

        $this->actingAs($this->user)->delete("/shop/registrations/{$pending->id}");

        $this->assertSame(0, PendingRegistration::query()->count());
        $this->assertSame(0, Customer::query()->count());
    }

    public function test_a_shop_cannot_accept_another_shops_pending()
    {
        $foreign = PendingRegistration::factory()->create(['shop_id' => Shop::factory()->create()->id]);

        $this->actingAs($this->user)->post("/shop/registrations/{$foreign->id}/accept")->assertNotFound();

        $this->assertDatabaseHas('pending_registrations', ['id' => $foreign->id]);
    }

    public function test_the_auto_accept_toggle_updates_the_shop()
    {
        $this->assertFalse($this->shop->fresh()->auto_accept_registrations);

        $this->actingAs($this->user)->put(route('shop.account.settings'), ['auto_accept_registrations' => true]);

        $this->assertTrue($this->shop->fresh()->auto_accept_registrations);
    }

    public function test_registrations_are_admin_gated()
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/shop/registrations')
            ->assertRedirect('/admin');
    }
}
