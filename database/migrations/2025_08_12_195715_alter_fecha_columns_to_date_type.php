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
        // Cambiar fecha de gastos_operativos de datetime a date
        DB::statement('ALTER TABLE gastos_operativos ALTER COLUMN fecha TYPE DATE USING fecha::DATE');
        
        // Cambiar fecha de servicios_realizados de datetime a date
        DB::statement('ALTER TABLE servicios_realizados ALTER COLUMN fecha TYPE DATE USING fecha::DATE');
        
        // La tabla ingresos_adicionales ya tiene fecha como DATE, no necesita cambio
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir gastos_operativos de date a datetime
        DB::statement('ALTER TABLE gastos_operativos ALTER COLUMN fecha TYPE TIMESTAMP USING fecha::TIMESTAMP');
        
        // Revertir servicios_realizados de date a datetime
        DB::statement('ALTER TABLE servicios_realizados ALTER COLUMN fecha TYPE TIMESTAMP USING fecha::TIMESTAMP');
    }
};
