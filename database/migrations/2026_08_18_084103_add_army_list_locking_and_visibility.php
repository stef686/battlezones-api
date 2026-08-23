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
        Schema::table('event_attendee_user', function (Blueprint $table): void {
            // Submission is what locks a list, so the timestamp is the lock.
            // A list typed in at registration by someone else is a draft until
            // the Player themselves submits it.
            $table->timestamp('army_list_submitted_at')->nullable()->after('army_list');
        });

        Schema::table('event_attendees', function (Blueprint $table): void {
            // The escape from the deadlock where an unclaimed partner never
            // opens their invite and hides their Captain's list all weekend.
            $table->timestamp('army_lists_revealed_at')->nullable()->after('allegiance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_attendee_user', function (Blueprint $table): void {
            $table->dropColumn('army_list_submitted_at');
        });

        Schema::table('event_attendees', function (Blueprint $table): void {
            $table->dropColumn('army_lists_revealed_at');
        });
    }
};
