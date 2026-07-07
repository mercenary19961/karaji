<?php

namespace Tests\Feature\Shop;

use App\Models\Message;
use App\Models\Shop;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InboxTest extends TestCase
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

    public function test_a_shop_sees_its_messages_and_opening_the_inbox_marks_them_read()
    {
        $unread = Message::factory()->create(['shop_id' => $this->shop->id, 'read_at' => null]);

        $this->actingAs($this->user)->get('/shop/messages')->assertInertia(
            fn (Assert $page) => $page->component('shop/messages')
                ->has('messages', 1)
                ->where('messages.0.unread', true)
        );

        // The mark-as-read side effect ran after the props were captured.
        $this->assertNotNull($unread->fresh()->read_at);
    }

    public function test_the_unread_badge_count_is_shared_and_clears_after_viewing()
    {
        Message::factory()->count(2)->create(['shop_id' => $this->shop->id, 'read_at' => null]);
        Message::factory()->create(['shop_id' => $this->shop->id, 'read_at' => now()]);

        $this->actingAs($this->user)->get('/shop')->assertInertia(
            fn (Assert $page) => $page->where('shopUnread', 2)
        );

        // Opening the inbox marks them read; the badge is then zero.
        $this->actingAs($this->user)->get('/shop/messages');
        $this->actingAs($this->user)->get('/shop')->assertInertia(
            fn (Assert $page) => $page->where('shopUnread', 0)
        );
    }

    public function test_a_shop_does_not_see_another_shops_messages()
    {
        $other = Shop::factory()->create();
        Message::factory()->create(['shop_id' => $other->id]);

        $this->actingAs($this->user)->get('/shop/messages')->assertInertia(
            fn (Assert $page) => $page->has('messages', 0)
        );
    }

    public function test_a_shop_can_send_a_suggestion()
    {
        $this->actingAs($this->user)
            ->post('/shop/suggestions', ['body' => 'Add a brake-fluid change service'])
            ->assertRedirect();

        $suggestion = Suggestion::query()->sole();
        $this->assertSame($this->shop->id, $suggestion->shop_id);
        $this->assertSame(Suggestion::STATUS_OPEN, $suggestion->status);
    }

    public function test_a_suggestion_requires_a_body()
    {
        $this->actingAs($this->user)
            ->post('/shop/suggestions', ['body' => ''])
            ->assertSessionHasErrors('body');

        $this->assertSame(0, Suggestion::query()->count());
    }

    public function test_a_shop_only_sees_its_own_suggestions()
    {
        $other = Shop::factory()->create();
        Suggestion::factory()->create(['shop_id' => $other->id]);
        Suggestion::factory()->create(['shop_id' => $this->shop->id]);

        $this->actingAs($this->user)->get('/shop/messages')->assertInertia(
            fn (Assert $page) => $page->has('suggestions', 1)
        );
    }

    public function test_admins_are_redirected_away_from_the_shop_inbox()
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/shop/messages')
            ->assertRedirect('/admin');
    }
}
