<?php

namespace Tests\Feature\Admin;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_edit_a_shops_identity_phone_and_reminder_pace()
    {
        $shop = Shop::factory()->create(['name' => 'كراج قديم', 'name_en' => null, 'area' => 'ماركا', 'area_en' => null]);

        $this->actingAs($this->admin())
            ->put("/admin/shops/{$shop->id}", [
                'name' => 'كراج أبو رامز',
                'name_en' => 'Abu Ramez Garage',
                'area' => 'ماركا',
                'area_en' => 'Marka',
                'phone' => '065551234',
                'default_daily_km' => 60,
            ])
            ->assertRedirect();

        $shop->refresh();
        $this->assertSame('كراج أبو رامز', $shop->name);
        $this->assertSame('Abu Ramez Garage', $shop->name_en);
        $this->assertSame('Marka', $shop->area_en);
        $this->assertSame('065551234', $shop->phone);
        $this->assertSame(60, $shop->default_daily_km);
    }

    public function test_the_daily_km_is_required_and_bounded()
    {
        $shop = Shop::factory()->create();

        $this->actingAs($this->admin())
            ->put("/admin/shops/{$shop->id}", ['name' => 'كراج', 'default_daily_km' => 0])
            ->assertSessionHasErrors('default_daily_km');
    }

    public function test_the_arabic_name_is_required()
    {
        $shop = Shop::factory()->create();

        $this->actingAs($this->admin())
            ->put("/admin/shops/{$shop->id}", ['name' => '', 'name_en' => 'X'])
            ->assertSessionHasErrors('name');
    }

    public function test_shop_users_cannot_edit_shops()
    {
        $shop = Shop::factory()->create();
        $shopUser = User::factory()->create(['shop_id' => $shop->id]);

        $this->actingAs($shopUser)
            ->put("/admin/shops/{$shop->id}", ['name' => 'Hacked'])
            ->assertRedirect('/shop');
    }
}
