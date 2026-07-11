<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visit_services', function (Blueprint $table) {
            // The amount actually charged for this service on this visit: a
            // snapshot of the shop's default at save time, editable per visit.
            // Null = the service was recorded without a price.
            $table->decimal('price', 8, 2)->nullable()->after('service_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('visit_services', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
