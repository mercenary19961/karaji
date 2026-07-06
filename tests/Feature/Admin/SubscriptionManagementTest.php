<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private Subscription $subscription;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shop = Shop::factory()->create();
        $this->subscription = Subscription::factory()->create(['shop_id' => $this->shop->id, 'plan' => 'basic', 'status' => 'active']);
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_change_the_plan_and_it_is_logged()
    {
        $this->actingAs($this->admin)
            ->put("/admin/shops/{$this->shop->id}/subscription", ['plan' => 'pro'])
            ->assertRedirect();

        $this->subscription->refresh();
        $this->assertSame('pro', $this->subscription->plan);
        $this->assertSame('25.00', $this->subscription->price_jod);

        $log = ActivityLog::query()->sole();
        $this->assertSame('Plan changed to Pro — 25 JOD/mo', $log->action);
        $this->assertSame('basic', $log->change_set['before']['plan']);
    }

    public function test_admin_can_suspend_and_reactivate_a_shop()
    {
        $this->actingAs($this->admin)->put("/admin/shops/{$this->shop->id}/subscription", ['status' => 'suspended']);
        $this->assertSame('suspended', $this->subscription->fresh()->status);

        $this->actingAs($this->admin)->put("/admin/shops/{$this->shop->id}/subscription", ['status' => 'active']);
        $this->assertSame('active', $this->subscription->fresh()->status);

        $this->assertSame(2, ActivityLog::query()->count());
    }

    public function test_extending_a_trial_moves_status_and_end_date()
    {
        $this->actingAs($this->admin)->post("/admin/shops/{$this->shop->id}/subscription/extend-trial");

        $this->subscription->refresh();
        $this->assertSame('trial', $this->subscription->status);
        $this->assertTrue($this->subscription->trial_ends_at->isSameDay(today()->addMonth()));
    }

    public function test_undo_restores_the_before_state_exactly_once()
    {
        $this->actingAs($this->admin)->put("/admin/shops/{$this->shop->id}/subscription", ['plan' => 'pro']);
        $log = ActivityLog::query()->sole();

        $this->actingAs($this->admin)->post("/admin/activity-logs/{$log->id}/undo");

        $this->assertSame('basic', $this->subscription->fresh()->plan);
        $this->assertNotNull($log->fresh()->undone_at);

        // A spent log entry cannot be undone again
        $this->subscription->update(['plan' => 'pro']);
        $this->actingAs($this->admin)
            ->post("/admin/activity-logs/{$log->id}/undo")
            ->assertSessionHas('error');
        $this->assertSame('pro', $this->subscription->fresh()->plan);
    }

    public function test_a_noop_update_writes_no_log()
    {
        $this->actingAs($this->admin)->put("/admin/shops/{$this->shop->id}/subscription", ['plan' => 'basic']);

        $this->assertSame(0, ActivityLog::query()->count());
    }

    public function test_shop_users_cannot_manage_subscriptions()
    {
        $shopUser = User::factory()->create(['shop_id' => $this->shop->id]);

        $this->actingAs($shopUser)
            ->put("/admin/shops/{$this->shop->id}/subscription", ['plan' => 'pro'])
            ->assertRedirect('/shop');

        $this->assertSame('basic', $this->subscription->fresh()->plan);
    }
}
