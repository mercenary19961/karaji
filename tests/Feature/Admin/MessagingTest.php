<?php

namespace Tests\Feature\Admin;

use App\Models\Message;
use App\Models\Shop;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_send_a_message_to_a_shop()
    {
        $shop = Shop::factory()->create();

        $this->actingAs($this->admin())
            ->post("/admin/shops/{$shop->id}/messages", ['title' => 'Welcome', 'body' => 'Your account is live'])
            ->assertRedirect();

        $message = Message::query()->sole();
        $this->assertSame($shop->id, $message->shop_id);
        $this->assertNull($message->read_at);
    }

    public function test_a_message_requires_a_title_and_body()
    {
        $shop = Shop::factory()->create();

        $this->actingAs($this->admin())
            ->post("/admin/shops/{$shop->id}/messages", ['title' => '', 'body' => ''])
            ->assertSessionHasErrors(['title', 'body']);

        $this->assertSame(0, Message::query()->count());
    }

    public function test_admin_sees_suggestions_from_all_shops()
    {
        $a = Shop::factory()->create();
        $b = Shop::factory()->create();
        Suggestion::factory()->create(['shop_id' => $a->id]);
        Suggestion::factory()->create(['shop_id' => $b->id]);

        $this->actingAs($this->admin())->get('/admin/suggestions')->assertInertia(
            fn (Assert $page) => $page->component('admin/suggestions')->has('suggestions', 2)
        );
    }

    public function test_admin_can_mark_a_suggestion_reviewed()
    {
        $shop = Shop::factory()->create();
        $suggestion = Suggestion::factory()->create(['shop_id' => $shop->id, 'status' => Suggestion::STATUS_OPEN]);

        $this->actingAs($this->admin())
            ->put("/admin/suggestions/{$suggestion->id}", ['status' => Suggestion::STATUS_REVIEWED])
            ->assertRedirect();

        $this->assertSame(Suggestion::STATUS_REVIEWED, $suggestion->fresh()->status);
    }

    public function test_shop_users_cannot_reach_admin_messaging()
    {
        $shop = Shop::factory()->create();
        $shopUser = User::factory()->create(['shop_id' => $shop->id]);

        $this->actingAs($shopUser)->get('/admin/suggestions')->assertRedirect('/shop');

        $this->actingAs($shopUser)
            ->post("/admin/shops/{$shop->id}/messages", ['title' => 'x', 'body' => 'y'])
            ->assertRedirect('/shop');

        $this->assertSame(0, Message::query()->count());
    }
}
