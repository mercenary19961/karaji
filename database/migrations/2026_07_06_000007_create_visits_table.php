<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('km');
            $table->decimal('price', 8, 2)->nullable();
            $table->string('oil_brand')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('visited_at');
            $table->timestamps();

            $table->index(['shop_id', 'visited_at']);
            $table->index('car_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
