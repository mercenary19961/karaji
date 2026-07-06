<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            // Visit that produced this reminder (e.g. oil change → next due)
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('oil'); // oil | license | seasonal | other
            $table->string('label')->nullable(); // display text, e.g. "تغيير زيت + فلتر هواء"
            $table->unsignedInteger('due_km')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->default('pending'); // pending | contacted | done | dismissed
            $table->dateTime('contacted_at')->nullable();
            $table->timestamps();

            // The daily call list: due/overdue pending reminders per shop
            $table->index(['shop_id', 'status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
