<?php

namespace Tests\Feature\Auth;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_defaults_to_arabic_rtl()
    {
        $this->get('/login')
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false);
    }

    public function test_the_toggle_switches_the_guest_pages_to_english()
    {
        $this->get(route('locale', 'en'));

        $this->get('/login')
            ->assertSee('lang="en"', false)
            ->assertSee('dir="ltr"', false);
    }

    public function test_an_invalid_locale_is_ignored()
    {
        $this->get(route('locale', 'fr'));

        $this->get('/login')->assertSee('lang="ar"', false);
    }

    public function test_the_shop_portal_stays_arabic_even_when_english_is_toggled()
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create(['shop_id' => $shop->id]);

        // Guest parks 'en' in the session, then that same session logs in
        $this->get(route('locale', 'en'));

        $this->actingAs($user)->get('/shop')
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false);
    }
}
