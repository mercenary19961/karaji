<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_registrations', function (Blueprint $table) {
            // The car's license (الترخيص) renewal month 1-12, if the customer knows
            // it — captured on the QR form so the shop owner doesn't have to.
            $table->unsignedTinyInteger('license_month')->nullable()->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('pending_registrations', function (Blueprint $table) {
            $table->dropColumn('license_month');
        });
    }
};
