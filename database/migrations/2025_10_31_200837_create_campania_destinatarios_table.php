<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campania_destinatarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campania_id')->constrained('campanias')->cascadeOnDelete();

            $table->string('nombre')->nullable(); // opcional
            $table->string('codigo_pais', 5); // obligatorio
            $table->string('numero'); // obligatorio
            $table->json('variables_json')->nullable();
            $table->enum('estado_envio', ['pendiente', 'enviado', 'error'])->default('pendiente');
            $table->timestamps();

            $table->index(['campania_id', 'estado_envio']);
            $table->unique(['campania_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campania_destinatarios');
    }
};
