<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lista_espera', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('servicio');
            $table->string('telefono')->nullable();
            $table->text('notas')->nullable();
            $table->date('fecha');
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index('fecha');
            $table->index(['fecha', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lista_espera');
    }
};
