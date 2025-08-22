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
        Schema::create('inventario', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('cantidad')->default(0);
            $table->decimal('costo_unitario', 10, 2)->default(0);
            $table->enum('estado', ['activo', 'inactivo', 'agotado'])->default('activo');
            $table->bigInteger('entidad_id')->unsigned();
            $table->bigInteger('creado_por')->unsigned()->nullable();
            $table->timestamps();

            // Índices
            $table->index(['entidad_id', 'estado']);
            $table->index(['cantidad']);

            // Claves foráneas
            $table->foreign('entidad_id')->references('id')->on('entidades')->onDelete('cascade');
            $table->foreign('creado_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};