<?php

namespace App\Support;

use Illuminate\Support\Facades\App;

/**
 * Locale-aware display formatting for the shop portal (ar | en). Arabic plural
 * forms follow counted-noun rules (3-10 plural, 11+ singular); English uses
 * the simple singular/plural split.
 */
class Format
{
    public static function km(int $km): string
    {
        return number_format($km);
    }

    public static function price(float|string|null $price): ?string
    {
        if ($price === null) {
            return null;
        }

        $amount = rtrim(rtrim(number_format((float) $price, 2), '0'), '.');

        return App::getLocale() === 'en' ? "{$amount} JOD" : "{$amount} د.أ";
    }

    public static function overdueDays(int $days): string
    {
        if (App::getLocale() === 'en') {
            return match (true) {
                $days <= 0 => 'Due today',
                $days === 1 => '1 day overdue',
                default => "{$days} days overdue",
            };
        }

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
        if (App::getLocale() === 'en') {
            return $months <= 1 ? 'Last visit 1 month ago' : "Last visit {$months} months ago";
        }

        return match (true) {
            $months <= 1 => 'آخر زيارة قبل شهر',
            $months === 2 => 'آخر زيارة قبل شهرين',
            $months <= 10 => "آخر زيارة قبل {$months} أشهر",
            default => "آخر زيارة قبل {$months} شهر",
        };
    }

    /**
     * Month name by number (1-12). Arabic uses the Levantine calendar names.
     */
    public static function monthName(int $month): string
    {
        $names = App::getLocale() === 'en'
            ? [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
            : [1 => 'كانون الثاني', 'شباط', 'آذار', 'نيسان', 'أيار', 'حزيران', 'تموز', 'آب', 'أيلول', 'تشرين الأول', 'تشرين الثاني', 'كانون الأول'];

        return $names[$month];
    }
}
