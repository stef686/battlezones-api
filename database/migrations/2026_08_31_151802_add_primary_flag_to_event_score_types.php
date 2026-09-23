<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_score_types', function (Blueprint $table) {
            $table->boolean('is_primary')->default(false)->after('is_derived');
        });

        /**
         * The Score Type a listing leads with is the one played for at the
         * table, so every existing Event is given its first non-derived
         * column. Match Points are worked out from the result rather than
         * played for, which is exactly what the flag exists to demote.
         */
        $firstPlayed = DB::table('event_score_types')
            ->where('is_derived', false)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get(['id', 'event_id'])
            ->unique('event_id')
            ->pluck('id');

        if ($firstPlayed->isNotEmpty()) {
            DB::table('event_score_types')->whereIn('id', $firstPlayed)->update(['is_primary' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('event_score_types', function (Blueprint $table) {
            $table->dropColumn('is_primary');
        });
    }
};
