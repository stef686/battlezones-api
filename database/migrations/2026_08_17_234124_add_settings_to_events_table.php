<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->json('settings')->nullable()->after('max_attendees');
        });

        DB::table('events')->update([
            'settings' => DB::raw("JSON_OBJECT('standings_visible', CAST(standings_visible AS JSON))"),
        ]);

        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn('standings_visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->boolean('standings_visible')->default(false)->after('max_attendees');
        });

        DB::table('events')->update([
            'standings_visible' => DB::raw("COALESCE(JSON_EXTRACT(settings, '$.standings_visible'), false)"),
        ]);

        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn('settings');
        });
    }
};
