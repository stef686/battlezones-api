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
        Schema::create('event_attendee_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_attendee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Denormalised so "a User enters an Event once" stays a database
            // guarantee rather than an application check. A duplicate entry
            // corrupts pairings and standings.
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();

            $table->foreignId('faction_id')->nullable()->constrained()->nullOnDelete();
            $table->text('army_list')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
            $table->unique(['event_attendee_id', 'user_id']);
        });

        DB::statement('
            INSERT INTO event_attendee_user (event_attendee_id, user_id, event_id, faction_id, army_list, created_at, updated_at)
            SELECT id, user_id, event_id, faction_id, army_list, created_at, updated_at
            FROM event_attendees
        ');

        Schema::table('event_attendees', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('event_id');
            $table->dropForeign(['user_id']);
            $table->dropForeign(['faction_id']);

            // MySQL is using the composite unique as the backing index for the
            // event_id foreign key, so give that key an index of its own first.
            $table->index('event_id');
        });

        // The composite unique also backed the user_id foreign key, so it can
        // only go once that constraint has been dropped.
        Schema::table('event_attendees', function (Blueprint $table): void {
            $table->dropUnique(['event_id', 'user_id']);
        });

        Schema::table('event_attendees', function (Blueprint $table): void {
            $table->dropColumn(['user_id', 'faction_id', 'army_list']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_attendees', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('faction_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->text('army_list')->nullable()->after('faction_id');
            $table->dropColumn('name');
        });

        DB::statement('
            UPDATE event_attendees a
            JOIN event_attendee_user m ON m.event_attendee_id = a.id
            SET a.user_id = m.user_id, a.faction_id = m.faction_id, a.army_list = m.army_list
        ');

        Schema::drop('event_attendee_user');
    }
};
