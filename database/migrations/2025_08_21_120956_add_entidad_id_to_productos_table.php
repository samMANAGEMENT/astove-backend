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
        Schema::table('productos', function (Blueprint $table) {
            // Agregar entidad_id como nullable primero
            $table->foreignId('entidad_id')->nullable()->constrained('entidades');
        });

        // Poblar entidad_id para registros existentes
        DB::statement("UPDATE productos SET entidad_id = 1 WHERE entidad_id IS NULL");

        // Hacer entidad_id no nullable
        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('entidad_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['entidad_id']);
            $table->dropColumn('entidad_id');
        });
    }
};
