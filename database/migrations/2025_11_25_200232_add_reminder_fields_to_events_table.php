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

            // Fecha y hora del recordatorio
            $table->dateTime('reminder_at')
                ->nullable()
                ->after('all_day');

            // Canal del recordatorio (whatsapp, sms, both)
            $table->string('reminder_channel', 20)
                ->nullable()
                ->after('reminder_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('reminder_at');
            $table->dropColumn('reminder_channel');
        });
    }
};
