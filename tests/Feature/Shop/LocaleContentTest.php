<?php

namespace Tests\Feature\Shop;

use App\Models\Announcement;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * The shop portal is bilingual: the brand name, the shop's own name/area, and
 * admin announcements all follow the shop user's UI locale (English falls back
 * to Arabic when an English value is missing).
 */
class LocaleContentTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shop = Shop::factory()->create([
            'name' => 'كراج أبو رامز',
            'name_en' => 'Abu Ramez Garage',
            'area' => 'ماركا',
            'area_en' => 'Marka',
        ]);
    }

    private function userWithLocale(string $locale): User
    {
        return User::factory()->create(['shop_id' => $this->shop->id, 'locale' => $locale]);
    }

    public function test_the_brand_name_is_arabic_for_an_arabic_user()
    {
        $this->actingAs($this->userWithLocale('ar'))->get('/shop')->assertInertia(
            fn (Assert $page) => $page->where('name', 'كراجي')->where('locale', 'ar')
        );
    }

    public function test_the_brand_name_is_english_for_an_english_user()
    {
        $this->actingAs($this->userWithLocale('en'))->get('/shop')->assertInertia(
            fn (Assert $page) => $page->where('name', 'Karaji')->where('locale', 'en')
        );
    }

    public function test_shop_name_and_area_follow_the_locale_but_the_arabic_name_is_always_available()
    {
        $this->actingAs($this->userWithLocale('en'))->get('/shop')->assertInertia(
            fn (Assert $page) => $page
                ->where('shop.name', 'Abu Ramez Garage')
                ->where('shop.area', 'Marka')
                ->where('shop.nameAr', 'كراج أبو رامز') // for the Arabic WhatsApp templates
        );

        $this->actingAs($this->userWithLocale('ar'))->get('/shop')->assertInertia(
            fn (Assert $page) => $page->where('shop.name', 'كراج أبو رامز')->where('shop.area', 'ماركا')
        );
    }

    public function test_english_shop_falls_back_to_arabic_name_when_no_english_is_set()
    {
        $shop = Shop::factory()->create(['name' => 'كراج بلا إنجليزي', 'name_en' => null]);
        $user = User::factory()->create(['shop_id' => $shop->id, 'locale' => 'en']);

        $this->actingAs($user)->get('/shop')->assertInertia(
            fn (Assert $page) => $page->where('shop.name', 'كراج بلا إنجليزي')
        );
    }

    public function test_announcement_text_follows_the_locale()
    {
        Announcement::factory()->create([
            'title' => 'فحص الشتاء',
            'title_en' => 'Winter check',
            'body' => 'افحص البطارية',
            'body_en' => 'Check the battery',
        ]);

        $this->actingAs($this->userWithLocale('en'))->get('/shop')->assertInertia(
            fn (Assert $page) => $page->where('announcements.0.title', 'Winter check')->where('announcements.0.body', 'Check the battery')
        );

        $this->actingAs($this->userWithLocale('ar'))->get('/shop')->assertInertia(
            fn (Assert $page) => $page->where('announcements.0.title', 'فحص الشتاء')->where('announcements.0.body', 'افحص البطارية')
        );
    }

    public function test_announcement_falls_back_to_arabic_when_english_is_missing()
    {
        Announcement::factory()->create(['title' => 'إعلان بالعربي فقط', 'title_en' => null]);

        $this->actingAs($this->userWithLocale('en'))->get('/shop')->assertInertia(
            fn (Assert $page) => $page->where('announcements.0.title', 'إعلان بالعربي فقط')
        );
    }
}
