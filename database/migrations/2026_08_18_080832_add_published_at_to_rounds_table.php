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
        Schema::table('rounds', function (Blueprint $table): void {
            // A Round is Draft while only organisers can see its Games, and
            // Live once Players can. Publishing itself belongs to its own
            // ticket; what is needed here is something for the rules that
            // freeze at the moment a Round goes Live to read.
            $table->timestamp('published_at')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rounds', function (Blueprint $table): void {
            $table->dropColumn('published_at');
        });
    }
};
