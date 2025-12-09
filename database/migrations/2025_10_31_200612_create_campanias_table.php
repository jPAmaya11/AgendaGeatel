<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | TABLA: campanias
        |--------------------------------------------------------------------------
        */
        Schema::create('campanias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->index();

            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();

            $table->enum('estado', ['pendiente', 'activa', 'finalizada', 'cancelada'])
                ->default('pendiente')
                ->index();

            $table->dateTime('fecha_inicio')->nullable()->index();
            $table->dateTime('fecha_fin')->nullable();

            // Totales generales
            $table->unsignedInteger('total_destinatarios')->default(0);
            $table->unsignedInteger('total_enviados')->default(0);
            $table->unsignedInteger('total_pendientes')->default(0);
            $table->unsignedInteger('total_errores')->default(0);

            // Retrasos por lote
            $table->boolean('usar_retraso_lote')->default(false);
            $table->unsignedSmallInteger('retraso_lote_min')->nullable();
            $table->unsignedSmallInteger('retraso_lote_max')->nullable();
            $table->unsignedSmallInteger('retraso_lote_cada')->nullable();

            // Retrasos por mensaje
            $table->boolean('usar_retraso_mensaje')->default(false);
            $table->unsignedSmallInteger('retraso_mensaje_min')->nullable();
            $table->unsignedSmallInteger('retraso_mensaje_max')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | TABLA: campania_sesiones
        | -> sesiones que el usuario selecciona en tiempo real
        |--------------------------------------------------------------------------
        */
        Schema::create('campania_sesiones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campania_id')
                ->constrained('campanias')
                ->cascadeOnDelete();

            // Nombre original de la sesión: Ventas1 / Sucursal_A / Soporte / etc.
            $table->string('nombre_sesion');

            // Número del bot usado en esa sesión (importante para evitar confusiones)
            $table->string('numero_bot')
                ->nullable();

            // Estado dinámico
            $table->enum('estado', ['activa', 'inactiva', 'caida'])
                ->default('activa');

            // Métricas
            $table->unsignedInteger('total_enviados')->default(0);
            $table->unsignedInteger('total_errores')->default(0);

            // Último ping desde WAHA
            $table->dateTime('ultimo_ping')->nullable();

            $table->timestamps();

            // Índices importantes
            $table->index(['campania_id', 'estado']);
            $table->index(['nombre_sesion', 'numero_bot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campania_sesiones');
        Schema::dropIfExists('campanias');
    }
};
