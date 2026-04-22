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
        Schema::create('game_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_attendee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_score_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('value', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['game_id', 'event_attendee_id', 'event_score_type_id'], 'game_scores_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_scores');
    }
};
