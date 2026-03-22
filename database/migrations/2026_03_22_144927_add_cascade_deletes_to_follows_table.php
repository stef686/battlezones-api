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
        Schema::table('follows', function (Blueprint $table) {
            $table->dropForeign(['follower_id']);
            $table->dropForeign(['following_id']);

            $table->foreignId('follower_id')->change()->constrained('users')->cascadeOnDelete();
            $table->foreignId('following_id')->change()->constrained('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('follows', function (Blueprint $table) {
            $table->dropForeign(['follower_id']);
            $table->dropForeign(['following_id']);

            $table->foreignId('follower_id')->change()->constrained('users');
            $table->foreignId('following_id')->change()->constrained('users');
        });
    }
};
