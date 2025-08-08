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
        Schema::table('productos', function (Blueprint $table) {
            // Primero renombrar la columna 'cantidad' por 'stock'
            $table->renameColumn('cantidad', 'stock');
        });

        Schema::table('productos', function (Blueprint $table) {
            // Luego ajustar las columnas decimales para tener precisión de 2 decimales
            $table->decimal('precio_unitario', 10, 2)->change();
            $table->decimal('costo_unitario', 10, 2)->change();
            
            // Agregar valor por defecto al stock
            $table->integer('stock')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Revertir los cambios de columnas decimales
            $table->decimal('precio_unitario')->change();
            $table->decimal('costo_unitario')->change();
            $table->integer('stock')->change();
        });

        Schema::table('productos', function (Blueprint $table) {
            // Revertir el renombre de la columna
            $table->renameColumn('stock', 'cantidad');
        });
    }
};
