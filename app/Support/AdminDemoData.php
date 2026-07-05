<?php

namespace App\Support;

/**
 * Static demo data for the admin portal screens, mirroring design/mockup-v1.html.
 * Same contract-first idea as ShopDemoData: these shapes are what the real
 * queries must return at schema-v1 time.
 */
class AdminDemoData
{
    public static function shops(): array
    {
        return [
            ['id' => 'abu-ramez', 'name' => 'Abu Ramez Garage', 'area' => 'Marka', 'status' => 'Active', 'visits' => 64, 'lastActive' => 'Today'],
            ['id' => 'bayader', 'name' => 'Bayader Car Care', 'area' => 'Bayader Wadi Seer', 'status' => 'Active', 'visits' => 51, 'lastActive' => 'Today'],
            ['id' => 'sweileh', 'name' => 'Sweileh Auto Center', 'area' => 'Sweileh', 'status' => 'Trial', 'visits' => 18, 'lastActive' => 'Yesterday'],
            ['id' => 'tabarbour', 'name' => 'Tabarbour Motors', 'area' => 'Tabarbour', 'status' => 'Active', 'visits' => 39, 'lastActive' => '2 days ago'],
            ['id' => 'wadi-saqra', 'name' => 'Wadi Saqra Service', 'area' => 'Wadi Saqra', 'status' => 'Suspended', 'visits' => 0, 'lastActive' => '3 weeks ago'],
            ['id' => 'marj-alhamam', 'name' => 'Marj Al Hamam Auto', 'area' => 'Marj Al Hamam', 'status' => 'Trial', 'visits' => 9, 'lastActive' => 'Today'],
        ];
    }

    public static function shopDetail(): array
    {
        return [
            'name' => 'Abu Ramez Garage',
            'area' => 'Marka',
            'stats' => [
                ['label' => 'Visits this month', 'value' => 64],
                ['label' => 'Cars on file', 'value' => 212],
                ['label' => 'Reminders sent', 'value' => 143],
                ['label' => 'WhatsApp summaries', 'value' => 89],
            ],
            'subscription' => [
                'status' => 'Active',
                'plan' => 'Basic — 15 JOD/mo',
                'plans' => ['Basic — 15 JOD/mo', 'Pro — 25 JOD/mo'],
                'renewsAt' => 'Aug 1, 2026',
            ],
            'activity' => [
                ['id' => 'a1', 'text' => 'Plan changed to Basic by admin', 'at' => 'Today 10:22', 'undoable' => false],
                ['id' => 'a2', 'text' => 'Shop suspended by admin', 'at' => 'Jul 3, 16:05', 'undoable' => true],
                ['id' => 'a3', 'text' => 'WhatsApp quota raised to 500/mo', 'at' => 'Jul 1, 09:14', 'undoable' => false],
                ['id' => 'a4', 'text' => 'Shop owner reset password', 'at' => 'Jun 28, 13:40', 'undoable' => false],
            ],
        ];
    }
}
