<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_attendees', function (Blueprint $table): void {
            // One square, normalised on upload like a Banner is. A single
            // variant because it is only ever drawn small: a list row, a
            // standings row, a pairing.
            $table->string('avatar_path')->nullable()->after('allegiance');
        });
    }

    public function down(): void
    {
        Schema::table('event_attendees', function (Blueprint $table): void {
            $table->dropColumn('avatar_path');
        });
    }
};
