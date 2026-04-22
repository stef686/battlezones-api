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
        Schema::create('event_standing_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_standing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_score_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('value', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['event_standing_id', 'event_score_type_id'], 'standing_scores_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_standing_scores');
    }
};
