<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* ===========================================================
         * TABLA PRINCIPAL DE CONEXIONES WAHA
         * =========================================================== */
        Schema::create('waha_conexiones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('host');

            $table->text('token_api');

            $table->enum('estado', ['activo', 'inactivo', 'error'])->default('inactivo');
            $table->dateTime('ultimo_ping')->nullable();

            $table->foreignId('user_id_admin')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['estado', 'ultimo_ping']);
        });

        /* ===========================================================
         * TABLA PARA ASIGNAR FILTROS A USUARIOS POR CONEXIÓN
         * =========================================================== */
        Schema::create('conexion_usuario_filtros', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('waha_conexion_id')
                ->constrained('waha_conexiones')
                ->cascadeOnDelete();

            $table->string('filtro')->nullable(); // Puede repetirse entre conexiones

            $table->timestamps();

            // Evita que el mismo usuario tenga la misma conexión dos veces
            $table->unique(['user_id', 'waha_conexion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conexion_usuario_filtros');
        Schema::dropIfExists('waha_conexiones');
    }
};
