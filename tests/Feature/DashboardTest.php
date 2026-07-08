<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_visiting_the_root_are_sent_to_login()
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_shop_users_land_on_the_shop_portal()
    {
        $shop = Shop::factory()->create();
        $this->actingAs(User::factory()->create(['shop_id' => $shop->id]));

        $this->get('/')->assertRedirect('/shop');
        $this->get('/dashboard')->assertRedirect('/shop');
    }

    public function test_admins_land_on_the_admin_portal()
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/')->assertRedirect('/admin');
        $this->get('/dashboard')->assertRedirect('/admin');
    }
}
