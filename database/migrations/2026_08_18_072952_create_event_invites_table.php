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
        Schema::create('event_invites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();

            // The account exists from the moment the invite is sent, so this
            // foreign key is always real rather than filled in on accept.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Pre-set when a Captain invites a partner into their party; null
            // when an Organiser invites a Captain who has yet to register one.
            $table->foreignId('event_attendee_id')->nullable()->constrained()->cascadeOnDelete();

            $table->foreignId('invited_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role');

            // Only the hash is stored; the plain token lives in the email.
            // Null where the invitee already had a claimed account: they log
            // in as themselves, and a second credential would bypass that.
            $table->string('token', 64)->nullable()->unique();

            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            // One outstanding credential per person per Event; re-inviting
            // reissues that row rather than leaving a second live token.
            $table->unique(['event_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_invites');
    }
};
