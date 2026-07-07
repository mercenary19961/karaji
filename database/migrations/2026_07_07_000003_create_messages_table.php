<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Direct admin -> shop messages (targeted to one shop). Broadcast to all
        // shops is a separate concern handled by announcements (dashboard).
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->dateTime('read_at')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
