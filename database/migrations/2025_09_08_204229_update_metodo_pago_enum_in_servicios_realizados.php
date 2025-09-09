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
        // Eliminar la restricción CHECK existente
        DB::statement("ALTER TABLE servicios_realizados DROP CONSTRAINT servicios_realizados_metodo_pago_check");
        
        // Crear una nueva restricción CHECK que incluya 'mixto'
        DB::statement("ALTER TABLE servicios_realizados ADD CONSTRAINT servicios_realizados_metodo_pago_check CHECK (metodo_pago IN ('efectivo', 'transferencia', 'mixto'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la nueva restricción CHECK
        DB::statement("ALTER TABLE servicios_realizados DROP CONSTRAINT servicios_realizados_metodo_pago_check");
        
        // Restaurar la restricción CHECK original
        DB::statement("ALTER TABLE servicios_realizados ADD CONSTRAINT servicios_realizados_metodo_pago_check CHECK (metodo_pago IN ('efectivo', 'transferencia'))");
    }
};

