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
            $table->foreignId('campania_id')->constrained('campanias')->cascadeOnDelete();
            $table->string('numero_cliente');
            $table->string('numero_bot');
            $table->enum('tipo_wsp_cliente', ['personal', 'empresarial'])->default('personal');
            $table->enum('formato_mensaje', ['texto', 'imagen', 'video', 'documento'])->default('texto');
            $table->text('mensaje')->nullable();
            $table->dateTime('fecha_registro')->nullable();
            $table->timestamps();

            // Índices útiles para reportes y búsquedas
            $table->index(['campania_id', 'fecha_registro']);
            $table->index('numero_cliente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_respuestas_clientes');
    }
};
