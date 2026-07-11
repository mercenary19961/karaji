<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained()->cascadeOnDelete();
            // The shop's default charge for this service, in JOD. Pre-fills the
            // per-service price on the visit form (still editable per visit).
            $table->decimal('price', 8, 2);
            $table->timestamps();

            $table->unique(['shop_id', 'service_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_prices');
    }
};
