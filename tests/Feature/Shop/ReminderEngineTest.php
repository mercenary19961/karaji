<?php

namespace Tests\Feature\Shop;

use App\Models\Car;
use App\Models\Reminder;
use App\Models\ServiceType;
use App\Models\Shop;
use App\Models\Visit;
use App\Services\Reminders\ReminderEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReminderEngineTest extends TestCase
{
    use RefreshDatabase;

    private ReminderEngine $engine;

    private Shop $shop;

    private ServiceType $oilChange;

    protected function setUp(): void
    {
        parent::setUp();

        $this->engine = app(ReminderEngine::class);
        $this->shop = Shop::factory()->create(['default_daily_km' => 40]);
        $this->oilChange = ServiceType::factory()->create(['name' => ServiceType::OIL_CHANGE]);
    }

    private function oilVisit(Car $car, int $km, string $when, string $type): Visit
    {
        $visit = Visit::factory()->create([
            'shop_id' => $this->shop->id,
            'car_id' => $car->id,
            'km' => $km,
            'oil_type' => $type,
            'visited_at' => Carbon::parse($when),
        ]);
        $visit->services()->attach($this->oilChange->id);

        return $visit;
    }

    public function test_synthetic_and_mineral_use_different_km_intervals()
    {
        $mineral = Car::factory()->create(['shop_id' => $this->shop->id]);
        $this->oilVisit($mineral, 50000, 'now', 'mineral');
        $this->engine->scheduleOilReminder($mineral);

        $synthetic = Car::factory()->create(['shop_id' => $this->shop->id]);
        $this->oilVisit($synthetic, 50000, 'now', 'synthetic');
        $this->engine->scheduleOilReminder($synthetic);

        $this->assertSame(50000 + 4000, $mineral->pendingOilReminder()->sole()->due_km);
        $this->assertSame(50000 + 10000, $synthetic->pendingOilReminder()->sole()->due_km);
    }

    public function test_due_date_uses_the_shop_default_pace_for_a_first_visit()
    {
        $car = Car::factory()->create(['shop_id' => $this->shop->id]);
        $this->oilVisit($car, 50000, '2026-01-01', 'mineral');

        $this->engine->scheduleOilReminder($car);

        // No history → 40 km/day default. mineral 4000 km / 40 = 100 days,
        // under the 4-month cap, so the km pace wins.
        $due = $car->pendingOilReminder()->sole()->due_date;
        $this->assertTrue($due->isSameDay(Carbon::parse('2026-01-01')->addDays(100)));
    }

    public function test_a_fast_driver_gets_an_earlier_due_date_than_a_slow_one()
    {
        // ~200 km/day: two visits 100 days apart, 20,000 km apart
        $fast = Car::factory()->create(['shop_id' => $this->shop->id]);
        $this->oilVisit($fast, 30000, '2025-09-23', 'synthetic');
        $this->oilVisit($fast, 50000, '2026-01-01', 'synthetic');
        $this->engine->scheduleOilReminder($fast);

        // Shop-default pace (40 km/day) car, same latest visit
        $slow = Car::factory()->create(['shop_id' => $this->shop->id]);
        $this->oilVisit($slow, 50000, '2026-01-01', 'synthetic');
        $this->engine->scheduleOilReminder($slow);

        $this->assertTrue(
            $fast->pendingOilReminder()->sole()->due_date->lt($slow->pendingOilReminder()->sole()->due_date),
        );
    }

    public function test_the_time_cap_bounds_a_very_slow_driver()
    {
        // Barely-driven car: mineral time cap (4 months) must win over the
        // enormous km-pace ETA.
        $car = Car::factory()->create(['shop_id' => $this->shop->id]);
        $this->oilVisit($car, 10000, '2025-07-01', 'mineral');
        $this->oilVisit($car, 10100, '2026-01-01', 'mineral'); // 100 km in ~6 months
        $this->engine->scheduleOilReminder($car);

        $due = $car->pendingOilReminder()->sole()->due_date;
        $this->assertTrue($due->isSameDay(Carbon::parse('2026-01-01')->addMonths(4)));
    }

    public function test_license_reminders_generate_within_the_lead_window()
    {
        // License month = next month → inside the 30-day lead window
        $soon = Car::factory()->create(['shop_id' => $this->shop->id, 'license_month' => today()->addMonth()->month]);
        // License month = 6 months out → outside the window
        $later = Car::factory()->create(['shop_id' => $this->shop->id, 'license_month' => today()->addMonths(6)->month]);

        $created = $this->engine->generateLicenseReminders();

        $this->assertSame(1, $created);
        $this->assertSame(1, $soon->reminders()->where('type', 'license')->count());
        $this->assertSame(0, $later->reminders()->where('type', 'license')->count());
    }

    public function test_license_generation_is_idempotent()
    {
        Car::factory()->create(['shop_id' => $this->shop->id, 'license_month' => today()->addMonth()->month]);

        $this->engine->generateLicenseReminders();
        $this->engine->generateLicenseReminders();

        $this->assertSame(1, Reminder::query()->where('type', 'license')->count());
    }

    public function test_generate_command_reports_created_count()
    {
        Car::factory()->create(['shop_id' => $this->shop->id, 'license_month' => today()->addMonth()->month]);

        $this->artisan('reminders:generate')
            ->expectsOutputToContain('License reminders created: 1')
            ->assertSuccessful();
    }
}
