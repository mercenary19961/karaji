<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // `name`/`area` hold the Arabic (default) identity; the *_en columns are
        // the optional English forms shown when the UI is in English. Nullable
        // so the English UI falls back to the Arabic value when unset.
        Schema::table('shops', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('area_en')->nullable()->after('area');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'area_en']);
        });
    }
};
