<?php

namespace Tests\Feature\Shop;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShopPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get('/shop')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_view_all_shop_screens()
    {
        $this->actingAs(User::factory()->create());

        $screens = [
            '/shop' => 'shop/dashboard',
            '/shop/visits/new' => 'shop/new-visit',
            '/shop/cars/demo' => 'shop/car',
            '/shop/reminders' => 'shop/reminders',
            '/shop/analytics' => 'shop/analytics',
        ];

        foreach ($screens as $uri => $component) {
            $this->get($uri)
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page->component($component)->has('shop.name'));
        }
    }
}
