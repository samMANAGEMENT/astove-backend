<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Revertir los cambios de la migración anterior
        // En la lógica del usuario: precio_unitario = costo de compra, costo_unitario = precio de venta
        // Pero la migración anterior los intercambió, así que los revertimos
        DB::statement("
            UPDATE productos 
            SET 
                precio_unitario = costo_unitario,
                costo_unitario = precio_unitario
            WHERE precio_unitario > costo_unitario
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Volver a aplicar el intercambio si es necesario
        DB::statement("
            UPDATE productos 
            SET 
                precio_unitario = costo_unitario,
                costo_unitario = precio_unitario
            WHERE costo_unitario > precio_unitario
        ");
    }
};
