<?php

namespace Tests\Feature\Admin;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_view_admin_screens()
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/shops')->has('shops', 6));

        $this->get('/admin/shops/demo')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/shop-detail')->has('shop.name'));
    }

    public function test_admin_pages_render_in_english_ltr()
    {
        // SetAdminLocale flips the blade shell to lang="en" dir="ltr"
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertSee('lang="en"', false)
            ->assertSee('dir="ltr"', false);

        // Shop portal stays Arabic RTL (same process — guards the locale leak)
        $this->actingAs(User::factory()->create(['shop_id' => Shop::factory()->create()->id]))
            ->get('/shop')
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false);
    }
}
