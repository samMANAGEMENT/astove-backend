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
        // Corregir productos donde el costo_unitario es mayor que precio_unitario
        // Esto indica que los valores están invertidos
        DB::statement("
            UPDATE productos 
            SET 
                precio_unitario = costo_unitario,
                costo_unitario = precio_unitario
            WHERE costo_unitario > precio_unitario
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir el cambio (aunque esto podría causar problemas si se han agregado nuevos productos)
        DB::statement("
            UPDATE productos 
            SET 
                precio_unitario = costo_unitario,
                costo_unitario = precio_unitario
            WHERE precio_unitario > costo_unitario
        ");
    }
};
