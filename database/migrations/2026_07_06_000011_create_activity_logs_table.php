<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->nullableMorphs('subject');
            // before/after snapshots powering undo (hardrock ActivityLogService
            // pattern). NOT named `changes` — that collides with Eloquent's
            // internal Model::$changes property inside model methods.
            $table->json('change_set')->nullable();
            $table->dateTime('undone_at')->nullable();
            $table->timestamps();

            $table->index('shop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
