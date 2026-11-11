<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Event Colour: one `#rrggbb` accent, null for the platform's own.
 *
 * Only the chosen colour is stored. The scale the SPA paints with is built
 * from it on the client, so changing how that scale is drawn never needs a
 * data migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->char('colour', 7)->nullable()->after('banner_small_path');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn('colour');
        });
    }
};
