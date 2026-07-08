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

    public function test_the_shop_portal_defaults_to_arabic_and_ignores_a_guest_session_toggle()
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create(['shop_id' => $shop->id]); // no locale preference

        // Guest parks 'en' in the session, then that same session logs in;
        // the shop reads the user's (null) preference, not the session.
        $this->get(route('locale', 'en'));

        $this->actingAs($user)->get('/shop')
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false);
    }

    public function test_a_shop_user_with_an_english_preference_sees_english_ltr()
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create(['shop_id' => $shop->id, 'locale' => 'en']);

        $this->actingAs($user)->get('/shop')
            ->assertSee('lang="en"', false)
            ->assertSee('dir="ltr"', false);
    }

    public function test_the_language_toggle_persists_on_the_shop_user()
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create(['shop_id' => $shop->id]);

        $this->actingAs($user)->get(route('locale', 'en'));

        $this->assertSame('en', $user->fresh()->locale);
        $this->actingAs($user)->get('/shop')->assertSee('lang="en"', false);
    }
}
