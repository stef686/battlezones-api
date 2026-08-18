<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('event_attendees', function (Blueprint $table): void {
            // A real column rather than a custom field response: Allegiance is
            // a hard pairing constraint, and pairing cannot read a stringly
            // typed EAV answer and stay testable. Null where the Event does
            // not divide the field.
            $table->string('allegiance')->nullable()->after('name');

            $table->index(['event_id', 'allegiance']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_attendees', function (Blueprint $table): void {
            $table->dropIndex(['event_id', 'allegiance']);
            $table->dropColumn('allegiance');
        });
    }
};
