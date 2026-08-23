<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two independent fields, not one.
 *
 * Someone walking the display table ticks teams off before anyone numbers
 * them, and the screen has to work one-handed.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('event_attendees', function (Blueprint $table) {
            $table->boolean('painting_entered')->default(false)->after('checked_in_at');
            $table->unsignedSmallInteger('display_number')->nullable()->after('painting_entered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_attendees', function (Blueprint $table) {
            $table->dropColumn(['painting_entered', 'display_number']);
        });
    }
};
