<?php

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
        Schema::table('users', function (Blueprint $table): void {
            // An invited Player has an account before they ever set a password.
            // A null password must never authenticate.
            $table->string('password')->nullable()->change();
            $table->timestamp('claimed_at')->nullable()->after('email_verified_at');
        });

        DB::table('users')->update(['claimed_at' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->whereNull('password')->update(['password' => '']);

        Schema::table('users', function (Blueprint $table): void {
            $table->string('password')->nullable(false)->change();
            $table->dropColumn('claimed_at');
        });
    }
};
