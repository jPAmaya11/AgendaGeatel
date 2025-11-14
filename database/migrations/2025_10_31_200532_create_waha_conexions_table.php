<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waha_conexiones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('host');
            $table->text('token_api');
            $table->enum('estado', ['activo', 'inactivo', 'error'])->default('inactivo');
            $table->dateTime('ultimo_ping')->nullable();
            $table->foreignId('user_id_admin')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waha_conexiones');
    }
};
