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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_system_id')->constrained()->restrictOnDelete();
            $table->foreignId('club_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->string('pairing_format')->default('swiss');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('venue_name')->nullable();
            $table->string('venue_address')->nullable();
            $table->string('venue_city')->nullable();
            $table->string('venue_country', 2)->nullable();
            $table->unsignedInteger('max_attendees')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('starts_at');
            $table->index('game_system_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
