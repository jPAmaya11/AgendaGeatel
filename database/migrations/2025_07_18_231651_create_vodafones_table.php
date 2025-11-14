<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vodafone', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('dni_nif_cif')->nullable();
            $table->string('id_cliente')->nullable();
            $table->text('observacion_smart')->nullable();
            $table->text('oferta_comercial')->nullable();
            $table->string('operador_actual')->nullable();
            $table->string('telefono_contacto')->nullable();
            $table->string('nombre_cliente')->nullable();
            $table->text('direccion_instalacion')->nullable();
            $table->date('fecha_creacion')->nullable();
            $table->date('fecha_cierre')->nullable();
            $table->text('observaciones_back_office')->nullable();
            $table->text('tipificaciones')->nullable();
            $table->text('observaciones_operaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vodafone');
    }
};
