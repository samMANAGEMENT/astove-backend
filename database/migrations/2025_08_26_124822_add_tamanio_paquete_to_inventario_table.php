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
        Schema::table('inventario', function (Blueprint $table) {
            $table->integer('tamanio_paquete')->nullable()->after('estado')->comment('Tamaño del paquete para este artículo (ej: 100 para paquetes de 100 unidades)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventario', function (Blueprint $table) {
            $table->dropColumn('tamanio_paquete');
        });
    }
};
