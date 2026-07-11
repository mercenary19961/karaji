<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            // Raw customer-submitted intake, awaiting the shop owner's accept/reject
            $table->string('name');
            $table->string('phone', 20);
            $table->string('plate', 20);
            $table->string('label')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_registrations');
    }
};
