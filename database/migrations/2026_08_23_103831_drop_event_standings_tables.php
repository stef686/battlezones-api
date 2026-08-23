<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Standings are computed from Games on read, so the stored rows they replace
 * only ever went stale.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('event_standing_scores');
        Schema::dropIfExists('event_standings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('event_standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_attendee_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->timestamps();

            $table->unique(['event_id', 'event_attendee_id']);
            $table->index(['event_id', 'position']);
        });

        Schema::create('event_standing_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_standing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_score_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('value', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['event_standing_id', 'event_score_type_id'], 'standing_scores_unique');
        });
    }
};
