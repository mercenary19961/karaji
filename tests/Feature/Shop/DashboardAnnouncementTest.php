<?php

namespace Tests\Feature\Shop;

use App\Models\Announcement;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shop = Shop::factory()->create();
        $this->user = User::factory()->create(['shop_id' => $this->shop->id]);
    }

    private function dashboardAnnouncementCount(): int
    {
        $count = 0;
        $this->actingAs($this->user)->get('/shop')->assertInertia(function (Assert $page) use (&$count) {
            $count = count($page->toArray()['props']['announcements']);
        });

        return $count;
    }

    public function test_a_shop_sees_broadcasts_and_its_own_targeted_announcements()
    {
        Announcement::factory()->create(['title' => 'Broadcast']);
        Announcement::factory()->create(['title' => 'Mine', 'shop_id' => $this->shop->id]);

        $this->assertSame(2, $this->dashboardAnnouncementCount());
    }

    public function test_a_shop_does_not_see_another_shops_targeted_announcement()
    {
        $other = Shop::factory()->create();
        Announcement::factory()->create(['shop_id' => $other->id]);

        $this->assertSame(0, $this->dashboardAnnouncementCount());
    }

    public function test_inactive_and_expired_announcements_are_hidden()
    {
        Announcement::factory()->inactive()->create();
        Announcement::factory()->expired()->create();

        $this->assertSame(0, $this->dashboardAnnouncementCount());
    }

    public function test_a_future_dated_announcement_is_hidden_until_it_starts()
    {
        Announcement::factory()->create(['starts_at' => now()->addWeek()->toDateString()]);

        $this->assertSame(0, $this->dashboardAnnouncementCount());
    }

    public function test_a_shop_can_dismiss_an_announcement_and_it_stays_hidden()
    {
        $announcement = Announcement::factory()->create();
        $this->assertSame(1, $this->dashboardAnnouncementCount());

        $this->actingAs($this->user)
            ->post("/shop/announcements/{$announcement->id}/dismiss")
            ->assertRedirect();

        $this->assertSame(0, $this->dashboardAnnouncementCount());
    }

    public function test_dismissing_is_per_shop_only()
    {
        $announcement = Announcement::factory()->create();
        $this->actingAs($this->user)->post("/shop/announcements/{$announcement->id}/dismiss");

        // A different shop still sees the broadcast.
        $otherShop = Shop::factory()->create();
        $otherUser = User::factory()->create(['shop_id' => $otherShop->id]);

        $count = 0;
        $this->actingAs($otherUser)->get('/shop')->assertInertia(function (Assert $page) use (&$count) {
            $count = count($page->toArray()['props']['announcements']);
        });

        $this->assertSame(1, $count);
    }

    public function test_dismissing_twice_is_a_no_op()
    {
        $announcement = Announcement::factory()->create();

        $this->actingAs($this->user)->post("/shop/announcements/{$announcement->id}/dismiss");
        $this->actingAs($this->user)->post("/shop/announcements/{$announcement->id}/dismiss")->assertRedirect();

        $this->assertSame(1, $announcement->dismissedBy()->count());
        $this->assertSame(0, $this->dashboardAnnouncementCount());
    }
}
