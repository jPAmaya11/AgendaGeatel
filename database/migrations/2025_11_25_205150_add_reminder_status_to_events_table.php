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
        Schema::table('events', function (Blueprint $table) {
            // Fecha y hora en que se envió el recordatorio (si ya se envió)
            $table->dateTime('reminder_sent_at')
                ->nullable()
                ->after('reminder_channel');

            // Estado del recordatorio: pending | sent | failed
            $table->string('reminder_status', 20)
                ->default('pending')
                ->after('reminder_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('reminder_sent_at');
            $table->dropColumn('reminder_status');
        });
    }
};
