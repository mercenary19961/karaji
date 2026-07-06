<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change-log v2 (retab-stores port): split before/after snapshots
        // (dirty fields only on updates), a human label for the admin list, and
        // a self-link marking entries produced by reverting another entry —
        // which makes reverts first-class history and gives redo for free.
        // Snapshot columns are NOT named `changes` — that collides with
        // Eloquent's internal Model::$changes property inside model methods.
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('action'); // created | updated | deleted
            $table->nullableMorphs('subject');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('label')->nullable();
            $table->foreignId('reverts_log_id')->nullable()->constrained('activity_logs')->nullOnDelete();
            $table->dateTime('reverted_at')->nullable();
            $table->foreignId('reverted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('shop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
