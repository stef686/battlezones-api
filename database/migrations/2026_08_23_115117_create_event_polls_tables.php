<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Polls are rows rather than code.
 *
 * "Best sportsman" and "best display board" are this same Poll with a
 * different eligibility rule, so hardcoding each vote would have meant a
 * migration for every award an Organiser thinks of.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->unsignedTinyInteger('votes_per_player')->default(1);
            $table->timestamps();

            $table->index(['event_id', 'type']);
        });

        Schema::create('event_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('voter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_event_attendee_id')->constrained('event_attendees')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['event_poll_id', 'voter_user_id', 'subject_event_attendee_id'], 'event_votes_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_votes');
        Schema::dropIfExists('event_polls');
    }
};
