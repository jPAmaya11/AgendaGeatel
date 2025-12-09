<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campania_mensajes', function (Blueprint $table) {
            $table->id();

            // Relación con la campaña
            $table->foreignId('campania_id')
                ->constrained('campanias')
                ->cascadeOnDelete();

            // Orden solo para referencia del mensaje enviado
            $table->unsignedSmallInteger('orden')->default(1);

            // Contenido del mensaje
            $table->text('mensaje');

            // Tipo de mensaje
            $table->enum('tipo_mensaje', ['texto', 'imagen', 'video', 'documento'])->default('texto');

            // URL de archivo o página web (opcional)
            $table->string('url_archivo', 500)->nullable();

            $table->timestamps();

            // Índices
            $table->index(['campania_id', 'orden']);
            $table->index('tipo_mensaje');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campania_mensajes');
    }
};
