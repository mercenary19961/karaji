<?php

namespace App\Console\Commands;

use App\Services\Reminders\ReminderEngine;
use Illuminate\Console\Command;

class GenerateReminders extends Command
{
    protected $signature = 'reminders:generate';

    protected $description = 'Generate due reminders (license renewals) across all shops';

    public function handle(ReminderEngine $engine): int
    {
        // Runs unauthenticated → tenancy scope is off by design (cross-shop).
        $created = $engine->generateLicenseReminders();

        $this->info("License reminders created: {$created}");

        return self::SUCCESS;
    }
}
