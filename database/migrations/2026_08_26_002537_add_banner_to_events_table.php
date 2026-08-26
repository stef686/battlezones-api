<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two columns rather than one.
 *
 * A Banner is stored as two normalised WebP variants, and both filenames are
 * UUIDs — deriving the small path from the large one would mean encoding a
 * naming convention in string manipulation, which is exactly the sort of rule
 * that quietly stops being true. This is the shape Photo already uses for its
 * file and its thumbnail.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->string('banner_path')->nullable()->after('description');
            $table->string('banner_small_path')->nullable()->after('banner_path');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn(['banner_path', 'banner_small_path']);
        });
    }
};
