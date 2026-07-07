<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // `title`/`body` are the Arabic (default) copy; the *_en columns are the
        // English copy shown when the shop's UI is in English. Nullable so an
        // Arabic-only announcement still renders (falls back) in English.
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->text('body_en')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'body_en']);
        });
    }
};
