<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            // Revenue moved to per-service prices on the visit_services pivot;
            // a visit's total is now the sum of those. The single visit price is
            // retired (never deployed) rather than left as a second source of truth.
            $table->dropColumn('price');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->nullable();
        });
    }
};
