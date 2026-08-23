<?php

use App\Enums\RegistrationMode;
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
        Schema::table('events', function (Blueprint $table): void {
            $table->unsignedTinyInteger('attendee_size')->default(1)->after('max_attendees');
            $table->string('registration_mode')->default(RegistrationMode::Open->value)->after('attendee_size');
            $table->timestamp('registration_closes_at')->nullable()->after('registration_mode');
            $table->string('timezone')->default('UTC')->after('registration_closes_at');

            $table->index(['status', 'registration_mode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropIndex(['status', 'registration_mode']);
            $table->dropColumn(['attendee_size', 'registration_mode', 'registration_closes_at', 'timezone']);
        });
    }
};
