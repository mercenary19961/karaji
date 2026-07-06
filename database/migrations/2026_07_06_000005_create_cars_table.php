<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('plate');
            // Free text ("كيا سبورتاج 2019") — garage owners type it once, we don't
            // force make/model/year structure in v1
            $table->string('label')->nullable();
            // Annual license renewal (الترخيص) month, 1-12
            $table->unsignedTinyInteger('license_month')->nullable();
            $table->timestamps();

            // The counter moment: lookup by plate within a shop
            $table->unique(['shop_id', 'plate']);
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
