<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Reminder;
use App\Models\ServiceType;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\Suggestion;
use App\Models\User;
use App\Models\Visit;
use App\Services\Reminders\ReminderEngine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds the SaaS operator account, the global service-type chips, and one
 * demo shop whose data mirrors design/mockup-v1.html — so the portal screens
 * look exactly like the validated mockup once wired to real queries.
 *
 * Logins: admin@example.com / password (admin) · shop@example.com / password (shop).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        $this->seedServiceTypes();
        $this->seedDemoShop();
        $this->seedOtherShops();

        // A seasonal broadcast so the shop dashboard shows a live announcement
        Announcement::factory()->create([
            'title' => 'فحص الشتاء 🚗❄️',
            'title_en' => 'Winter check 🚗❄️',
            'body' => 'ذكّر زبائنك يفحصوا البطارية والإطارات قبل الشتا · عرض خاص هالشهر.',
            'body_en' => 'Remind your customers to check the battery and tires before winter · special offer this month.',
        ]);
    }

    /**
     * A few more shops in varied subscription states so the admin portal
     * list looks like a running SaaS.
     */
    private function seedOtherShops(): void
    {
        $others = [
            ['name' => 'كراج البيادر', 'name_en' => 'Al-Bayader Garage', 'area' => 'البيادر', 'area_en' => 'Al-Bayader', 'email' => 'shop2@example.com', 'status' => 'active', 'plan' => 'basic', 'visits' => 5],
            ['name' => 'مركز صويلح للسيارات', 'name_en' => 'Sweileh Auto Center', 'area' => 'صويلح', 'area_en' => 'Sweileh', 'email' => 'shop3@example.com', 'status' => 'trial', 'plan' => 'basic', 'visits' => 2],
            ['name' => 'كراج طبربور', 'name_en' => 'Tabarbour Garage', 'area' => 'طبربور', 'area_en' => 'Tabarbour', 'email' => 'shop4@example.com', 'status' => 'active', 'plan' => 'pro', 'visits' => 4],
            ['name' => 'ورشة وادي صقرة', 'name_en' => 'Wadi Saqra Workshop', 'area' => 'وادي صقرة', 'area_en' => 'Wadi Saqra', 'email' => 'shop5@example.com', 'status' => 'suspended', 'plan' => 'basic', 'visits' => 0],
        ];

        foreach ($others as $data) {
            $shop = Shop::factory()->create([
                'name' => $data['name'],
                'name_en' => $data['name_en'],
                'area' => $data['area'],
                'area_en' => $data['area_en'],
            ]);

            User::factory()->create(['name' => $data['name'], 'email' => $data['email'], 'shop_id' => $shop->id]);

            Subscription::factory()->create([
                'shop_id' => $shop->id,
                'plan' => $data['plan'],
                'status' => $data['status'],
                'price_jod' => Subscription::PLANS[$data['plan']]['price'],
                'renews_at' => $data['status'] === 'active' ? now()->addMonth()->toDateString() : null,
                'trial_ends_at' => $data['status'] === 'trial' ? now()->addWeeks(2)->toDateString() : null,
            ]);

            if ($data['visits'] > 0) {
                Visit::factory()->count($data['visits'])->create([
                    'shop_id' => $shop->id,
                    'visited_at' => fake()->dateTimeBetween(now()->startOfMonth()),
                ]);
            }
        }
    }

    private function seedServiceTypes(): void
    {
        // The global default chips, bilingual (English shows in the English UI).
        $chips = [
            ['ar' => 'تغيير زيت', 'en' => 'Oil change'],
            ['ar' => 'فلتر زيت', 'en' => 'Oil filter'],
            ['ar' => 'فلتر هواء', 'en' => 'Air filter'],
            ['ar' => 'فلتر مكيف', 'en' => 'A/C filter'],
            ['ar' => 'فحص فرامل', 'en' => 'Brake check'],
            ['ar' => 'بطارية', 'en' => 'Battery'],
            ['ar' => 'دواليب', 'en' => 'Tires'],
            ['ar' => 'أخرى', 'en' => 'Other'],
        ];

        foreach ($chips as $order => $chip) {
            ServiceType::factory()->create(['name' => $chip['ar'], 'name_en' => $chip['en'], 'sort_order' => $order]);
        }
    }

    private function seedDemoShop(): void
    {
        $shop = Shop::factory()->create([
            'name' => 'كراج أبو رامز',
            'name_en' => 'Abu Ramez Garage',
            'area' => 'ماركا',
            'area_en' => 'Marka',
        ]);

        User::factory()->create([
            'name' => 'أبو رامز',
            'email' => 'shop@example.com',
            'shop_id' => $shop->id,
        ]);

        Subscription::factory()->create([
            'shop_id' => $shop->id,
            'plan' => 'basic',
            'status' => 'active',
            'price_jod' => 15,
            'renews_at' => '2026-08-01',
        ]);

        $serviceIds = ServiceType::query()->pluck('id', 'name');

        // The mockup's hero car: full history + a scheduled oil reminder
        $sportage = $this->seedCar($shop, 'أبو محمد', '0795123456', 'كيا سبورتاج 2019', '22-14853', 11, 'Abu Mohammad', 'Kia Sportage 2019');

        // Synthetic (Mobil 5W-30) → the engine schedules next oil at 82,500 +
        // 10,000 = 92,500 km, matching the mockup's "الزيت القادم".
        $visits = [
            ['visited_at' => '2026-03-05', 'km' => 82500, 'price' => 28, 'oil_brand' => 'Mobil 5W-30', 'oil_type' => 'synthetic', 'services' => ['تغيير زيت', 'فلتر زيت']],
            ['visited_at' => '2025-11-18', 'km' => 77900, 'price' => 46, 'oil_brand' => 'Mobil 5W-30', 'oil_type' => 'synthetic', 'services' => ['تغيير زيت', 'فلتر هواء', 'فحص فرامل']],
            ['visited_at' => '2025-07-02', 'km' => 72400, 'price' => 25, 'oil_brand' => 'Castrol 5W-40', 'oil_type' => 'synthetic', 'services' => ['تغيير زيت']],
            ['visited_at' => '2025-02-15', 'km' => 67100, 'price' => 95, 'oil_brand' => 'Castrol 5W-40', 'oil_type' => 'synthetic', 'services' => ['تغيير زيت', 'بطارية']],
        ];

        foreach ($visits as $data) {
            $visit = Visit::factory()->create([
                'shop_id' => $shop->id,
                'car_id' => $sportage->id,
                'km' => $data['km'],
                'price' => $data['price'],
                'oil_brand' => $data['oil_brand'],
                'oil_type' => $data['oil_type'],
                'visited_at' => Carbon::parse($data['visited_at']),
            ]);
            $visit->services()->attach($serviceIds->only($data['services'])->values());
        }

        app(ReminderEngine::class)->scheduleOilReminder($sportage);

        // The call list: due/overdue reminders, most overdue first
        $callList = [
            ['name' => 'معاذ الخطيب', 'name_en' => 'Moath Al-Khatib', 'phone' => '0796234567', 'label' => 'تويوتا كامري 2017', 'label_en' => 'Toyota Camry 2017', 'plate' => '13-45210', 'licenseMonth' => null, 'type' => 'oil', 'due' => 'تغيير زيت', 'due_en' => 'Oil change', 'overdueDays' => 12],
            ['name' => 'أم علي', 'name_en' => 'Umm Ali', 'phone' => '0790345678', 'label' => 'هيونداي إلنترا 2020', 'label_en' => 'Hyundai Elantra 2020', 'plate' => '40-21873', 'licenseMonth' => null, 'type' => 'oil', 'due' => 'تغيير زيت + فلتر هواء', 'due_en' => 'Oil change + air filter', 'overdueDays' => 8],
            ['name' => 'أبو خالد', 'name_en' => 'Abu Khaled', 'phone' => '0777456789', 'label' => 'مرسيدس E200 2016', 'label_en' => 'Mercedes E200 2016', 'plate' => '5-98412', 'licenseMonth' => 7, 'type' => 'license', 'due' => 'تجديد ترخيص · شهر 7/2026', 'due_en' => 'License renewal · 7/2026', 'overdueDays' => 5],
            ['name' => 'سامر العمري', 'name_en' => 'Samer Al-Amri', 'phone' => '0798567890', 'label' => 'ميتسوبيشي لانسر 2015', 'label_en' => 'Mitsubishi Lancer 2015', 'plate' => '17-30654', 'licenseMonth' => null, 'type' => 'seasonal', 'due' => 'فحص الشتاء (موسمي)', 'due_en' => 'Winter check (seasonal)', 'overdueDays' => 0],
        ];

        foreach ($callList as $entry) {
            $car = $this->seedCar($shop, $entry['name'], $entry['phone'], $entry['label'], $entry['plate'], $entry['licenseMonth'], $entry['name_en'], $entry['label_en']);

            Reminder::factory()->create([
                'shop_id' => $shop->id,
                'car_id' => $car->id,
                'type' => $entry['type'],
                'label' => $entry['due'],
                'label_en' => $entry['due_en'],
                'due_km' => null,
                'due_date' => now()->subDays($entry['overdueDays'])->toDateString(),
            ]);
        }

        // "Customers you're losing": last visit 6+ months ago, no pending reminder
        $lost = [
            ['name' => 'أبو ليث', 'name_en' => 'Abu Laith', 'phone' => '0795987654', 'label' => 'تويوتا كامري 2014', 'label_en' => 'Toyota Camry 2014', 'plate' => '22-77140', 'monthsAgo' => 7],
            ['name' => 'أم يزن', 'name_en' => 'Umm Yazan', 'phone' => '0796876543', 'label' => 'هيونداي إلنترا 2016', 'label_en' => 'Hyundai Elantra 2016', 'plate' => '36-55021', 'monthsAgo' => 8],
            ['name' => 'سامر العمري', 'name_en' => 'Samer Al-Amri', 'phone' => '0798567890', 'label' => null, 'label_en' => null, 'plate' => '17-30654', 'monthsAgo' => 11],
        ];

        foreach ($lost as $entry) {
            $car = Car::query()->where('shop_id', $shop->id)->where('plate', $entry['plate'])->first()
                ?? $this->seedCar($shop, $entry['name'], $entry['phone'], $entry['label'], $entry['plate'], null, $entry['name_en'], $entry['label_en']);

            $visit = Visit::factory()->create([
                'shop_id' => $shop->id,
                'car_id' => $car->id,
                'km' => fake()->numberBetween(60000, 180000),
                'price' => 25,
                'oil_brand' => 'Total 10W-40',
                'visited_at' => now()->subMonths($entry['monthsAgo']),
            ]);
            $visit->services()->attach($serviceIds->only(['تغيير زيت'])->values());
        }

        // Admin → shop message (one unread, so the inbox badge shows) + a
        // suggestion the shop sent back, so both directions have demo content.
        Message::factory()->create([
            'shop_id' => $shop->id,
            'title' => 'أهلين فيك ببرنامج الكراج 👋',
            'body' => 'يا هلا أبو رامز، حسابك صار شغّال. أي استفسار احكِ معنا واتساب وإحنا بخدمتك.',
            'read_at' => now(),
        ]);
        Message::factory()->create([
            'shop_id' => $shop->id,
            'title' => 'تحديث جديد ع البرنامج',
            'body' => 'ضفنا صفحة التقارير الشهرية. شوفها من قائمة "التقارير" تحت.',
            'read_at' => null,
        ]);

        Suggestion::factory()->create([
            'shop_id' => $shop->id,
            'body' => 'ياريت تضيفوا خدمة تغيير زيت الفرامل، لأنه حالياً في بس تغيير زيت المحرك.',
            'status' => Suggestion::STATUS_OPEN,
        ]);
    }

    /**
     * English name/label are seed-only polish so the demo reads well in English.
     * Real shops enter Arabic only, leaving *_en null → the English UI falls back
     * to the Arabic value (a customer's name isn't translated).
     */
    private function seedCar(Shop $shop, string $customerName, string $phone, ?string $label, string $plate, ?int $licenseMonth, ?string $customerNameEn = null, ?string $labelEn = null): Car
    {
        $customer = Customer::factory()->create([
            'shop_id' => $shop->id,
            'name' => $customerName,
            'name_en' => $customerNameEn,
            'phone' => $phone,
        ]);

        return Car::factory()->create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'plate' => $plate,
            'label' => $label,
            'label_en' => $labelEn,
            'license_month' => $licenseMonth,
        ]);
    }
}
