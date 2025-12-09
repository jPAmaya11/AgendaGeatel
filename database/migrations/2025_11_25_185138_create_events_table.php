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
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            // Usuario dueño del evento
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Datos generales del evento
            $table->string('title');
            $table->text('description')->nullable();

            // Tipo de evento (reunión, pendiente, cita, etc.)
            $table->enum('type', ['meeting', 'task', 'reminder', 'other'])->default('other');

            // Fechas y horas
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();

            // Estado del evento
            $table->enum('status', ['pending', 'done', 'cancelled'])->default('pending');

            // Para sincronizar con Google Calendar
            $table->boolean('is_synced_google')->default(false);
            $table->string('google_event_id')->nullable();

            // Metadatos extra
            $table->string('location')->nullable();
            $table->string('priority')->nullable(); // baja, media, alta

            $table->string('reminder_type')->nullable(); // 'exact', 'before', 'recurrent'
            $table->integer('reminder_interval')->nullable(); // Minutos u horas
            $table->string('reminder_unit')->nullable(); // 'minutes', 'hours'
            $table->timestamp('reminder_start_at')->nullable(); // Fecha de inicio para recordatorios recurrentes
            $table->string('reminder_frequency')->nullable(); // 'once', 'repeat'

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
