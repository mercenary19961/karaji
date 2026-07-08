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

    public function test_admin_can_edit_a_shops_arabic_and_english_name_and_area()
    {
        $shop = Shop::factory()->create(['name' => 'كراج قديم', 'name_en' => null, 'area' => 'ماركا', 'area_en' => null]);

        $this->actingAs($this->admin())
            ->put("/admin/shops/{$shop->id}", [
                'name' => 'كراج أبو رامز',
                'name_en' => 'Abu Ramez Garage',
                'area' => 'ماركا',
                'area_en' => 'Marka',
            ])
            ->assertRedirect();

        $shop->refresh();
        $this->assertSame('كراج أبو رامز', $shop->name);
        $this->assertSame('Abu Ramez Garage', $shop->name_en);
        $this->assertSame('Marka', $shop->area_en);
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
