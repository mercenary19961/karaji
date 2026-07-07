<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional English forms for the content that renders in the (bilingual)
     * shop portal. All nullable — the English UI falls back to the Arabic value
     * when unset, which is the common case for free-text data a shop enters in
     * Arabic. Service types are the one genuinely bilingual vocabulary; the rest
     * are mainly so the demo looks complete in English.
     */
    public function up(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
        });

        Schema::table('cars', function (Blueprint $table) {
            $table->string('label_en')->nullable()->after('label');
        });

        Schema::table('reminders', function (Blueprint $table) {
            $table->string('label_en')->nullable()->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('service_types', fn (Blueprint $table) => $table->dropColumn('name_en'));
        Schema::table('customers', fn (Blueprint $table) => $table->dropColumn('name_en'));
        Schema::table('cars', fn (Blueprint $table) => $table->dropColumn('label_en'));
        Schema::table('reminders', fn (Blueprint $table) => $table->dropColumn('label_en'));
    }
};
