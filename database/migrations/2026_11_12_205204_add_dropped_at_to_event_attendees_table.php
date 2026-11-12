<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_attendees', function (Blueprint $table): void {
            // When an Organiser marked the team as having left part-way
            // through. A timestamp rather than a flag, because when they
            // left is the only thing worth knowing about it; null is in.
            $table->timestamp('dropped_at')->nullable()->after('checked_in_at');
        });
    }

    public function down(): void
    {
        Schema::table('event_attendees', function (Blueprint $table): void {
            $table->dropColumn('dropped_at');
        });
    }
};
