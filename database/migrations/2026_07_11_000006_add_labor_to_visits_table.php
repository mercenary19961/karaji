<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            // The shop's labor / handwork charge (أجرة اليد) for the visit, on top
            // of the per-service parts prices. Null = not charged.
            $table->decimal('labor', 8, 2)->nullable()->after('km');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn('labor');
        });
    }
};
