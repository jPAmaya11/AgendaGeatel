<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_respuestas_clientes', function (Blueprint $table) {
            $table->id();

            // Campaña a la que pertenece la respuesta
            $table->foreignId('campania_id')
                ->constrained('campanias')
                ->cascadeOnDelete();

            // Datos del cliente que respondió
            $table->string('codigo_pais_cliente', 10);
            $table->string('numero_cliente');

            // Contenido del mensaje
            $table->text('mensaje')->nullable();

            // Fecha de registro del mensaje recibido
            $table->dateTime('fecha_registro')->nullable();

            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index(['campania_id', 'fecha_registro']);
            $table->index(['codigo_pais_cliente', 'numero_cliente']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_respuestas_clientes');
    }
};
