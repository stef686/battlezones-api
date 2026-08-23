<?php

use App\Enums\RoundStatus;
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
        Schema::table('rounds', function (Blueprint $table): void {
            $table->string('status')->default(RoundStatus::Draft->value)->after('name');
        });

        DB::table('rounds')
            ->whereNotNull('published_at')
            ->update(['status' => RoundStatus::Live->value]);

        // Publication is a state, not a moment: `published_at` said the same
        // thing as `status` and could disagree with it, and nothing reads the
        // timestamp itself.
        Schema::table('rounds', function (Blueprint $table): void {
            $table->dropColumn('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rounds', function (Blueprint $table): void {
            $table->timestamp('published_at')->nullable()->after('name');
        });

        DB::table('rounds')
            ->where('status', RoundStatus::Live->value)
            ->update(['published_at' => now()]);

        Schema::table('rounds', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
