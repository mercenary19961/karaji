<?php

use App\Models\Shop;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // Unguessable token in the public QR self-registration URL (/join/{token})
            $table->string('public_token', 48)->nullable()->unique()->after('default_daily_km');
            // When on, QR submissions become customers immediately; else they queue
            $table->boolean('auto_accept_registrations')->default(false)->after('public_token');
        });

        // Backfill tokens for any shops that already exist
        Shop::query()->whereNull('public_token')->get()->each(function (Shop $shop) {
            $shop->forceFill(['public_token' => Str::random(40)])->save();
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['public_token', 'auto_accept_registrations']);
        });
    }
};
