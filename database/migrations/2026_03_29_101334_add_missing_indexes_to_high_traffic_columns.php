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
        Schema::table('pending_email_changes', function (Blueprint $table) {
            $table->index('token');
        });

        Schema::table('pending_password_changes', function (Blueprint $table) {
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pending_email_changes', function (Blueprint $table) {
            $table->dropIndex(['token']);
        });

        Schema::table('pending_password_changes', function (Blueprint $table) {
            $table->dropIndex(['token']);
        });
    }
};
