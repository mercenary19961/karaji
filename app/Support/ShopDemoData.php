<?php

namespace App\Support;

/**
 * Static demo data for the shop portal screens, mirroring design/mockup-v1.html.
 *
 * Temporary by design: each method's return shape is the contract the real
 * controllers/models must honor at schema-v1 time, so swapping this class for
 * Eloquent queries is a controller-only change. Phone numbers are fake but
 * well-formed so tel:/wa.me deep links work in live demos.
 */
class ShopDemoData
{
    public static function shop(): array
    {
        return ['name' => 'كراج أبو رامز', 'area' => 'ماركا'];
    }

    public static function stats(): array
    {
        return ['todayVisits' => 7, 'dueCount' => 12, 'monthRevenue' => '1,840'];
    }

    public static function dueToday(): array
    {
        return [
            ['car' => 'تويوتا كامري', 'owner' => 'معاذ الخطيب', 'due' => 'تغيير زيت', 'overdueLabel' => 'متأخر 12 يوم'],
            ['car' => 'هيونداي إلنترا', 'owner' => 'أم علي', 'due' => 'تغيير زيت + فلتر هواء', 'overdueLabel' => 'متأخر 8 أيام'],
            ['car' => 'مرسيدس E200', 'owner' => 'أبو خالد', 'due' => 'تجديد ترخيص', 'overdueLabel' => 'متأخر 5 أيام'],
        ];
    }

    public static function car(): array
    {
        return [
            'label' => 'كيا سبورتاج 2019',
            'plate' => '22-14853',
            'owner' => 'أبو محمد',
            'phone' => '0795123456',
            'whatsapp' => '962795123456',
            'lastService' => 'آخر تغيير زيت: قبل 4 أشهر على عداد 82,500 كم',
            'licenseMonth' => '11/2026',
            'nextDue' => ['km' => '92,500', 'date' => '15/10/2026'],
            'visits' => [
                ['date' => '05/03/2026', 'km' => '82,500', 'price' => '28 د.أ', 'services' => ['تغيير زيت', 'فلتر زيت']],
                ['date' => '18/11/2025', 'km' => '77,900', 'price' => '46 د.أ', 'services' => ['تغيير زيت', 'فلتر هواء', 'فحص فرامل']],
                ['date' => '02/07/2025', 'km' => '72,400', 'price' => '25 د.أ', 'services' => ['تغيير زيت']],
                ['date' => '15/02/2025', 'km' => '67,100', 'price' => '95 د.أ', 'services' => ['تغيير زيت', 'بطارية']],
            ],
        ];
    }

    public static function serviceTypes(): array
    {
        return ['تغيير زيت', 'فلتر زيت', 'فلتر هواء', 'فلتر مكيف', 'فحص فرامل', 'بطارية', 'دواليب', 'أخرى'];
    }

    public static function oilBrands(): array
    {
        return ['Mobil 5W-30', 'Castrol 5W-40', 'Total 10W-40', 'Shell 5W-30', 'آخر'];
    }

    public static function reminders(): array
    {
        return [
            ['id' => 'r1', 'car' => 'تويوتا كامري 2017', 'owner' => 'معاذ الخطيب', 'phone' => '0796234567', 'whatsapp' => '962796234567', 'due' => 'تغيير زيت', 'overdueLabel' => 'متأخر 12 يوم'],
            ['id' => 'r2', 'car' => 'هيونداي إلنترا 2020', 'owner' => 'أم علي', 'phone' => '0790345678', 'whatsapp' => '962790345678', 'due' => 'تغيير زيت + فلتر هواء', 'overdueLabel' => 'متأخر 8 أيام'],
            ['id' => 'r3', 'car' => 'مرسيدس E200 2016', 'owner' => 'أبو خالد', 'phone' => '0777456789', 'whatsapp' => '962777456789', 'due' => 'تجديد ترخيص — شهر 7/2026', 'overdueLabel' => 'متأخر 5 أيام'],
            ['id' => 'r4', 'car' => 'ميتسوبيشي لانسر 2015', 'owner' => 'سامر العمري', 'phone' => '0798567890', 'whatsapp' => '962798567890', 'due' => 'فحص الشتاء (موسمي)', 'overdueLabel' => 'مستحق اليوم'],
        ];
    }

    public static function analytics(): array
    {
        return [
            'months' => [
                ['label' => 'شباط', 'visits' => 39],
                ['label' => 'آذار', 'visits' => 47],
                ['label' => 'نيسان', 'visits' => 33],
                ['label' => 'أيار', 'visits' => 56],
                ['label' => 'حزيران', 'visits' => 49],
                ['label' => 'تموز', 'visits' => 64],
            ],
            'topServices' => [
                ['label' => 'تغيير زيت', 'count' => 46],
                ['label' => 'فلتر هواء', 'count' => 19],
                ['label' => 'فحص فرامل', 'count' => 11],
                ['label' => 'بطارية', 'count' => 6],
            ],
            'lostCustomers' => [
                ['owner' => 'أبو ليث', 'car' => 'تويوتا كامري', 'lastVisit' => 'آخر زيارة قبل 7 أشهر', 'whatsapp' => '962795987654'],
                ['owner' => 'أم يزن', 'car' => 'هيونداي إلنترا', 'lastVisit' => 'آخر زيارة قبل 8 أشهر', 'whatsapp' => '962796876543'],
                ['owner' => 'سامر العمري', 'car' => 'ميتسوبيشي لانسر', 'lastVisit' => 'آخر زيارة قبل 11 شهر', 'whatsapp' => '962798567890'],
            ],
        ];
    }
}
