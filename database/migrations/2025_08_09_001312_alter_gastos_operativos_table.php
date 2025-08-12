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
        // Para PostgreSQL, necesitamos usar raw SQL para la conversión
        DB::statement('ALTER TABLE gastos_operativos ALTER COLUMN monto TYPE DECIMAL(15,2) USING monto::DECIMAL(15,2)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Para PostgreSQL, necesitamos usar raw SQL para la conversión inversa
        DB::statement('ALTER TABLE gastos_operativos ALTER COLUMN monto TYPE VARCHAR(255) USING monto::VARCHAR(255)');
    }
};
