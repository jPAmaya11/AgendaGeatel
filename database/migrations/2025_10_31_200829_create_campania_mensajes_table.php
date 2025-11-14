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
            $table->foreignId('campania_id')->constrained('campanias')->cascadeOnDelete();
            $table->text('mensaje');
            $table->enum('tipo_mensaje', ['texto', 'imagen', 'video', 'documento'])->default('texto');
            $table->string('url_archivo', 500)->nullable();
            $table->timestamps();

            $table->index('tipo_mensaje');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campania_mensajes');
    }
};
