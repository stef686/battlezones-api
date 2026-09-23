<?php

use App\Models\EventScoreType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_score_types', function (Blueprint $table) {
            $table->string('abbreviation', 8)->nullable()->after('name');
        });

        /**
         * Every existing column is given the heading the clients were already
         * deriving from its name, so the change is invisible until an
         * Organiser types something better: the Standings and the Game screens
         * have shown MP and VP since before the column existed.
         */
        DB::table('event_score_types')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->chunk(200, function ($scoreTypes): void {
                foreach ($scoreTypes as $scoreType) {
                    DB::table('event_score_types')
                        ->where('id', $scoreType->id)
                        ->update(['abbreviation' => EventScoreType::abbreviate($scoreType->name)]);
                }
            });

        // Not null from here: a column with no heading would leave a client
        // deriving one again, which is the fork this column exists to close.
        Schema::table('event_score_types', function (Blueprint $table) {
            $table->string('abbreviation', 8)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('event_score_types', function (Blueprint $table) {
            $table->dropColumn('abbreviation');
        });
    }
};
