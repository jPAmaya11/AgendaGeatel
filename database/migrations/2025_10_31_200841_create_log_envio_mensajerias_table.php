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

            // Guardamos el nombre de la sesión usada
            $table->string('waha_sesion_nombre')->nullable()->comment('Nombre de la sesión usada para el envío');

            $table->string('numero_bot');
            $table->string('numero_cliente');
            $table->text('mensaje_enviado')->nullable();
            $table->enum('estado', ['pendiente', 'enviado', 'entregado', 'leido', 'error'])->default('pendiente');
            $table->dateTime('fecha_envio')->nullable();
            $table->dateTime('fecha_entregado')->nullable();
            $table->dateTime('fecha_leido')->nullable();
            $table->timestamps();

            // índices para optimización de reportes
            $table->index(['campania_id', 'estado']);
            $table->index(['waha_sesion_nombre', 'fecha_envio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_envios_mensajeria');
    }
};
