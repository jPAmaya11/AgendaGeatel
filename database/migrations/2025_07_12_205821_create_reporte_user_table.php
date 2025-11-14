<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla pivote reporte_user
        Schema::create('reporte_user', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('reporte_id')
                ->constrained('reportes')
                ->cascadeOnDelete();
            $table->primary(['user_id', 'reporte_id']);
        });

        // Tabla pivote cartera_user
        Schema::create('cartera_user', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('cartera_id')
                ->constrained('carteras')
                ->cascadeOnDelete();
            $table->primary(['user_id', 'cartera_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cartera_user');
        Schema::dropIfExists('reporte_user');
    }
};
