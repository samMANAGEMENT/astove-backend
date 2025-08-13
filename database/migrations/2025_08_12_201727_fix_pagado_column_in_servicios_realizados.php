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
        // La columna ya es boolean, solo necesitamos asegurar que no haya valores nulos
        DB::statement("UPDATE servicios_realizados SET pagado = false WHERE pagado IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario revertir ya que solo estamos corrigiendo datos
    }
};
