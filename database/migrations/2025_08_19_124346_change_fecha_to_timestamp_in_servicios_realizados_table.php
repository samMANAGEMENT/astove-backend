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
        // Cambiar fecha de servicios_realizados de DATE a TIMESTAMP
        DB::statement('ALTER TABLE servicios_realizados ALTER COLUMN fecha TYPE TIMESTAMP USING fecha::TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir fecha de servicios_realizados de TIMESTAMP a DATE
        DB::statement('ALTER TABLE servicios_realizados ALTER COLUMN fecha TYPE DATE USING fecha::DATE');
    }
};
