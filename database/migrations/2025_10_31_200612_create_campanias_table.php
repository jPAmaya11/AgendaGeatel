<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campanias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->index();

            // Lista de nombres de sesiones seleccionadas
            $table->json('waha_sesiones')->nullable()->comment('Lista de nombres de sesiones usadas en WAHA');

            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();

            $table->enum('estado', ['pendiente', 'activa', 'finalizada', 'cancelada'])
                ->default('pendiente')
                ->index();

            $table->dateTime('fecha_inicio')->nullable()->index();
            $table->dateTime('fecha_fin')->nullable();

            $table->unsignedInteger('total_destinatarios')->default(0);
            $table->unsignedInteger('total_enviados')->default(0);
            $table->unsignedInteger('total_leidos')->default(0);
            $table->unsignedInteger('total_errores')->default(0);

            $table->boolean('usar_retraso_lote')->default(false);
            $table->unsignedSmallInteger('retraso_lote_min')->nullable();
            $table->unsignedSmallInteger('retraso_lote_max')->nullable();
            $table->unsignedSmallInteger('retraso_lote_cada')->nullable();

            $table->boolean('usar_retraso_mensaje')->default(false);
            $table->unsignedSmallInteger('retraso_mensaje_min')->nullable();
            $table->unsignedSmallInteger('retraso_mensaje_max')->nullable();

            $table->timestamps();
            $table->index(['estado', 'fecha_inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campanias');
    }
};
