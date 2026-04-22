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
        Schema::create('event_custom_field_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_attendee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_custom_field_id')->constrained()->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['event_attendee_id', 'event_custom_field_id'], 'attendee_field_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_custom_field_responses');
    }
};
