<?php

namespace App\Support;

/**
 * Arabic display formatting shared by the shop portal controllers.
 * Plural forms follow the mockup's copy: ٣-١٠ take the plural noun,
 * ١١+ take the singular (Arabic counted-noun rules).
 */
class Format
{
    public static function km(int $km): string
    {
        return number_format($km);
    }

    public static function price(?string $price): ?string
    {
        return $price === null ? null : rtrim(rtrim(number_format((float) $price, 2), '0'), '.').' د.أ';
    }

    public static function overdueDays(int $days): string
    {
        return match (true) {
            $days <= 0 => 'مستحق اليوم',
            $days === 1 => 'متأخر يوم',
            $days === 2 => 'متأخر يومين',
            $days <= 10 => "متأخر {$days} أيام",
            default => "متأخر {$days} يوم",
        };
    }

    public static function monthsAgo(int $months): string
    {
        return match (true) {
            $months <= 1 => 'آخر زيارة قبل شهر',
            $months === 2 => 'آخر زيارة قبل شهرين',
            $months <= 10 => "آخر زيارة قبل {$months} أشهر",
            default => "آخر زيارة قبل {$months} شهر",
        };
    }

    /**
     * Levantine month names, 1-indexed by month number.
     */
    public static function monthName(int $month): string
    {
        return [
            1 => 'كانون الثاني', 'شباط', 'آذار', 'نيسان', 'أيار', 'حزيران',
            'تموز', 'آب', 'أيلول', 'تشرين الأول', 'تشرين الثاني', 'كانون الأول',
        ][$month];
    }
}
