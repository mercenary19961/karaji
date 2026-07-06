<?php

namespace Tests\Feature\Admin;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_as_a_shop_and_return()
    {
        $shop = Shop::factory()->create();
        $shopUser = User::factory()->create(['shop_id' => $shop->id]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post("/admin/shops/{$shop->id}/impersonate")
            ->assertRedirect('/shop');

        $this->assertAuthenticatedAs($shopUser);
        $this->assertSame($admin->id, session('impersonator_id'));

        // The shop portal flags the impersonation for the banner
        $this->get('/shop')->assertInertia(fn ($page) => $page->where('impersonating', true));

        $this->post('/impersonation/leave')->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session('impersonator_id'));
    }

    public function test_impersonating_a_shop_without_users_fails_gracefully()
    {
        $shop = Shop::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from("/admin/shops/{$shop->id}")
            ->post("/admin/shops/{$shop->id}/impersonate")
            ->assertRedirect("/admin/shops/{$shop->id}")
            ->assertSessionHas('error');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_shop_users_cannot_impersonate()
    {
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();
        User::factory()->create(['shop_id' => $shopB->id]);
        $shopUser = User::factory()->create(['shop_id' => $shopA->id]);

        $this->actingAs($shopUser)
            ->post("/admin/shops/{$shopB->id}/impersonate")
            ->assertRedirect('/shop');

        $this->assertAuthenticatedAs($shopUser);
    }

    public function test_leaving_without_impersonating_goes_home()
    {
        $shop = Shop::factory()->create();
        $shopUser = User::factory()->create(['shop_id' => $shop->id]);

        $this->actingAs($shopUser)->post('/impersonation/leave')->assertRedirect('/');
        $this->assertAuthenticatedAs($shopUser);
    }
}
