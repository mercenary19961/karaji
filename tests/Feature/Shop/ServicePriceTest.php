<?php

namespace Tests\Feature\Shop;

use App\Models\ServicePrice;
use App\Models\ServiceType;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ServicePriceTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private User $user;

    private ServiceType $oilChange;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shop = Shop::factory()->create();
        $this->user = User::factory()->create(['shop_id' => $this->shop->id]);
        $this->oilChange = ServiceType::factory()->create(['name' => ServiceType::OIL_CHANGE]);
    }

    public function test_the_settings_page_lists_services_with_their_current_prices()
    {
        ServicePrice::factory()->create(['shop_id' => $this->shop->id, 'service_type_id' => $this->oilChange->id, 'price' => 20]);

        $this->actingAs($this->user)
            ->get('/shop/service-prices')
            ->assertInertia(fn (Assert $page) => $page
                ->component('shop/service-prices')
                ->has('services', 1)
                ->where('services.0.label', ServiceType::OIL_CHANGE)
                ->where('services.0.price', '20'));
    }

    public function test_saving_prices_creates_and_updates_defaults()
    {
        $this->actingAs($this->user)->put('/shop/service-prices', [
            'prices' => [$this->oilChange->id => 22],
        ]);

        $this->assertEquals(22, ServicePrice::query()->where('service_type_id', $this->oilChange->id)->sole()->price);

        // Saving again updates in place (no duplicate row)
        $this->actingAs($this->user)->put('/shop/service-prices', [
            'prices' => [$this->oilChange->id => 25],
        ]);

        $this->assertSame(1, ServicePrice::query()->count());
        $this->assertEquals(25, ServicePrice::query()->sole()->price);
    }

    public function test_clearing_a_price_removes_the_default()
    {
        ServicePrice::factory()->create(['shop_id' => $this->shop->id, 'service_type_id' => $this->oilChange->id, 'price' => 20]);

        $this->actingAs($this->user)->put('/shop/service-prices', [
            'prices' => [$this->oilChange->id => null],
        ]);

        $this->assertSame(0, ServicePrice::query()->count());
    }

    public function test_a_shop_cannot_set_a_price_for_another_shops_private_service()
    {
        $otherShop = Shop::factory()->create();
        $privateService = ServiceType::factory()->create(['shop_id' => $otherShop->id, 'name' => 'خدمة خاصة']);

        $this->actingAs($this->user)->put('/shop/service-prices', [
            'prices' => [$privateService->id => 30],
        ]);

        // Ignored — the service isn't available to this shop
        $this->assertSame(0, ServicePrice::query()->withoutGlobalScope('shop')->count());
    }

    public function test_service_prices_are_admin_gated()
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/shop/service-prices')
            ->assertRedirect('/admin');
    }
}
