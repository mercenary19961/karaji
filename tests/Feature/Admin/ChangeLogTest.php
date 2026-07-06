<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ChangeLog\ChangeLogService;
use App\Services\ChangeLog\RevertResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Compound-edit scenarios for the change-log v2 port — the cases that broke
 * Sky Amman's snapshot-based design (see CLAUDE.md → Reference Projects).
 */
class ChangeLogTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private Subscription $subscription;

    private User $admin;

    private ChangeLogService $changeLog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shop = Shop::factory()->create();
        $this->subscription = Subscription::factory()->create(['shop_id' => $this->shop->id, 'plan' => 'basic', 'status' => 'active']);
        $this->admin = User::factory()->admin()->create();
        $this->changeLog = app(ChangeLogService::class);
    }

    private function changePlan(string $plan): void
    {
        $this->actingAs($this->admin)->put("/admin/shops/{$this->shop->id}/subscription", ['plan' => $plan]);
    }

    public function test_updates_snapshot_only_dirty_fields()
    {
        $this->changePlan('pro');

        $log = ActivityLog::query()->sole();
        $this->assertEqualsCanonicalizing(['plan', 'price_jod'], array_keys($log->old_data));
        $this->assertArrayNotHasKey('status', $log->old_data);
        $this->assertSame('basic', $log->old_data['plan']);
        $this->assertSame('pro', $log->new_data['plan']);
    }

    public function test_reverting_writes_a_mirror_entry_and_stamps_the_original()
    {
        $this->changePlan('pro');
        $log = ActivityLog::query()->sole();

        $this->actingAs($this->admin)
            ->post("/admin/activity-logs/{$log->id}/undo")
            ->assertSessionHas('success');

        $this->assertSame('basic', $this->subscription->fresh()->plan);
        $this->assertNotNull($log->fresh()->reverted_at);
        $this->assertSame($this->admin->id, $log->fresh()->reverted_by);

        $mirror = ActivityLog::query()->whereNotNull('reverts_log_id')->sole();
        $this->assertSame($log->id, $mirror->reverts_log_id);
        $this->assertSame('pro', $mirror->old_data['plan']);
        $this->assertSame('basic', $mirror->new_data['plan']);
    }

    public function test_redo_is_reverting_the_mirror_entry()
    {
        $this->changePlan('pro');
        $original = ActivityLog::query()->sole();

        $this->actingAs($this->admin)->post("/admin/activity-logs/{$original->id}/undo");
        $mirror = ActivityLog::query()->whereNotNull('reverts_log_id')->sole();

        // Redo: revert the mirror → plan is pro again, history has 3 entries
        $this->actingAs($this->admin)->post("/admin/activity-logs/{$mirror->id}/undo");

        $this->assertSame('pro', $this->subscription->fresh()->plan);
        $this->assertSame(3, ActivityLog::query()->count());
    }

    public function test_reverting_a_stale_entry_conflicts_instead_of_clobbering()
    {
        $this->changePlan('pro');
        $first = ActivityLog::query()->sole();

        // A later edit now owns the field
        $this->changePlan('basic');

        $this->actingAs($this->admin)
            ->post("/admin/activity-logs/{$first->id}/undo")
            ->assertSessionHas('error');

        // Nothing changed, nothing stamped
        $this->assertSame('basic', $this->subscription->fresh()->plan);
        $this->assertNull($first->fresh()->reverted_at);
    }

    public function test_conflict_check_is_per_field_not_per_record()
    {
        $this->changePlan('pro');
        $planChange = ActivityLog::query()->sole();

        // A later edit to a DIFFERENT field must not block the plan revert
        $this->actingAs($this->admin)->put("/admin/shops/{$this->shop->id}/subscription", ['status' => 'suspended']);

        $this->actingAs($this->admin)
            ->post("/admin/activity-logs/{$planChange->id}/undo")
            ->assertSessionHas('success');

        $this->subscription->refresh();
        $this->assertSame('basic', $this->subscription->plan);
        $this->assertSame('suspended', $this->subscription->status); // later edit preserved
    }

    public function test_an_already_reverted_entry_cannot_be_reverted_again()
    {
        $this->changePlan('pro');
        $log = ActivityLog::query()->sole();

        $this->actingAs($this->admin)->post("/admin/activity-logs/{$log->id}/undo");
        $result = $this->changeLog->revert($log->fresh());

        $this->assertFalse($result->ok);
        $this->assertSame(RevertResult::REASON_ALREADY_REVERTED, $result->reason);
    }

    public function test_reverting_fails_honestly_when_the_subject_is_gone()
    {
        $this->changePlan('pro');
        $log = ActivityLog::query()->sole();

        $this->subscription->delete();

        $result = $this->changeLog->revert($log->fresh());

        $this->assertFalse($result->ok);
        $this->assertSame(RevertResult::REASON_SUBJECT_MISSING, $result->reason);
    }

    public function test_noop_updates_write_no_log()
    {
        $this->changePlan('basic');

        $this->assertSame(0, ActivityLog::query()->count());
    }
}
