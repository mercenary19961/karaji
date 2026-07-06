<?php

namespace Tests\Feature\Admin;

use App\Models\Shop;
use App\Models\Subscription;
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

    public function test_shop_users_are_redirected_to_their_portal()
    {
        $shop = Shop::factory()->create();
        $this->actingAs(User::factory()->create(['shop_id' => $shop->id]));

        $this->get('/admin')->assertRedirect('/shop');
    }

    public function test_admins_see_real_shops_with_subscription_status()
    {
        $shopA = Shop::factory()->create();
        Subscription::factory()->create(['shop_id' => $shopA->id, 'status' => 'active']);
        $shopB = Shop::factory()->create();
        Subscription::factory()->trial()->create(['shop_id' => $shopB->id]);

        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/shops')
                ->has('shops', 2)
                ->where('shops.0.status', fn ($status) => in_array($status, ['active', 'trial'], true)));
    }

    public function test_admins_see_a_shops_detail_with_stats_and_subscription()
    {
        $shop = Shop::factory()->create();
        Subscription::factory()->create(['shop_id' => $shop->id]);

        $this->actingAs(User::factory()->admin()->create());

        $this->get("/admin/shops/{$shop->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/shop-detail')
                ->where('shop.id', $shop->id)
                ->has('shop.stats', 4)
                ->where('shop.subscription.status', 'active'));
    }

    public function test_admin_pages_render_in_english_ltr()
    {
        // SetAdminLocale flips the blade shell to lang="en" dir="ltr"
        $this->actingAs(User::factory()->admin()->create())
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
