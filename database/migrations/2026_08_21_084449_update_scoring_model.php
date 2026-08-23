<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_score_types', function (Blueprint $table) {
            $table->boolean('is_derived')->default(false)->after('sort_direction');
            $table->unsignedSmallInteger('ranking_order')->nullable()->after('is_derived');
            $table->decimal('win_points', 8, 2)->nullable()->after('ranking_order');
            $table->decimal('draw_points', 8, 2)->nullable()->after('win_points');
            $table->decimal('loss_points', 8, 2)->nullable()->after('draw_points');
        });

        Schema::table('game_attendee', function (Blueprint $table) {
            $table->dropColumn('score');
        });
    }

    public function down(): void
    {
        Schema::table('event_score_types', function (Blueprint $table) {
            $table->dropColumn(['is_derived', 'ranking_order', 'win_points', 'draw_points', 'loss_points']);
        });

        Schema::table('game_attendee', function (Blueprint $table) {
            $table->integer('score')->nullable();
        });
    }
};
