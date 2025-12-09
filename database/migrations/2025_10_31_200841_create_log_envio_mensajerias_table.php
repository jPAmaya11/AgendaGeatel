<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_envios_mensajeria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campania_id')->constrained('campanias')->cascadeOnDelete();
            $table->foreignId('destinatario_id')->constrained('campania_destinatarios')->cascadeOnDelete();

            // Nombre corto de la sesión usada
            $table->string('sesion_waha')->nullable();

            $table->string('numero_bot')->nullable();
            $table->string('codigo_pais_cliente', 5)->nullable();
            $table->string('numero_cliente');

            $table->text('mensaje_enviado')->nullable();
            $table->enum('estado', ['pendiente', 'enviado', 'error'])->default('pendiente');
            $table->dateTime('fecha_envio')->nullable();
            $table->timestamps();

            // índices para optimización de reportes
            $table->index(['campania_id', 'estado']);
            $table->index(['sesion_waha', 'fecha_envio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_envios_mensajeria');
    }
};
