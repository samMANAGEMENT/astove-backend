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
        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained('inventario')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipo', ['entrada', 'salida']);
            $table->integer('cantidad_anterior');
            $table->integer('cantidad_movimiento');
            $table->integer('cantidad_nueva');
            $table->timestamps();
            
            // Índices para mejor rendimiento
            $table->index(['inventario_id', 'created_at']);
            $table->index(['usuario_id', 'created_at']);
            $table->index('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_movimientos');
    }
};
