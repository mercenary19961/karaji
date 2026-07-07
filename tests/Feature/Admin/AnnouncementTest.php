<?php

namespace Tests\Feature\Admin;

use App\Models\Announcement;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_publish_a_broadcast_announcement()
    {
        $this->actingAs($this->admin())
            ->post('/admin/announcements', ['title' => 'Winter check', 'body' => 'Season is coming'])
            ->assertRedirect('/admin/announcements');

        $announcement = Announcement::query()->sole();
        $this->assertNull($announcement->shop_id);
        $this->assertTrue($announcement->is_active);
    }

    public function test_admin_can_target_a_single_shop()
    {
        $shop = Shop::factory()->create();

        $this->actingAs($this->admin())
            ->post('/admin/announcements', ['title' => 'Just you', 'body' => 'Targeted', 'shop_id' => $shop->id]);

        $this->assertSame($shop->id, Announcement::query()->sole()->shop_id);
    }

    public function test_end_date_must_not_precede_start_date()
    {
        $this->actingAs($this->admin())
            ->post('/admin/announcements', [
                'title' => 'Bad window',
                'body' => 'x',
                'starts_at' => '2026-02-01',
                'ends_at' => '2026-01-01',
            ])
            ->assertSessionHasErrors('ends_at');

        $this->assertSame(0, Announcement::query()->count());
    }

    public function test_admin_can_toggle_and_delete()
    {
        $announcement = Announcement::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)->post("/admin/announcements/{$announcement->id}/toggle");
        $this->assertFalse($announcement->fresh()->is_active);

        $this->actingAs($admin)->delete("/admin/announcements/{$announcement->id}");
        $this->assertSame(0, Announcement::query()->count());
    }

    public function test_shop_users_cannot_reach_admin_announcements()
    {
        $shop = Shop::factory()->create();

        $this->actingAs(User::factory()->create(['shop_id' => $shop->id]))
            ->get('/admin/announcements')
            ->assertRedirect('/shop');
    }

    public function test_admin_can_publish_a_bilingual_announcement()
    {
        $this->actingAs($this->admin())->post('/admin/announcements', [
            'title' => 'فحص الشتاء',
            'title_en' => 'Winter check',
            'body' => 'افحص البطارية',
            'body_en' => 'Check the battery',
        ]);

        $announcement = Announcement::query()->sole();
        $this->assertSame('Winter check', $announcement->title_en);
        $this->assertSame('Check the battery', $announcement->body_en);
    }

    public function test_the_announcements_page_exposes_the_seasonal_templates()
    {
        $this->actingAs($this->admin())->get('/admin/announcements')->assertInertia(function (Assert $page) {
            $templates = $page->toArray()['props']['templates'];
            $this->assertNotEmpty($templates);
            // Every template carries both languages for title and body.
            foreach ($templates as $template) {
                $this->assertArrayHasKey('ar', $template['title']);
                $this->assertArrayHasKey('en', $template['title']);
                $this->assertArrayHasKey('ar', $template['body']);
                $this->assertArrayHasKey('en', $template['body']);
            }
        });
    }
}
