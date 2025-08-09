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
        Schema::table('ingresos_adicionales', function (Blueprint $table) {
            $table->unsignedBigInteger('operador_id')->nullable()->after('empleado_id');
            $table->unsignedBigInteger('servicio_realizado_id')->nullable()->after('operador_id');
            $table->foreign('operador_id')->references('id')->on('operadores')->onDelete('set null');
            $table->foreign('servicio_realizado_id')->references('id')->on('servicios_realizados')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingresos_adicionales', function (Blueprint $table) {
            $table->dropForeign(['operador_id']);
            $table->dropForeign(['servicio_realizado_id']);
            $table->dropColumn(['operador_id', 'servicio_realizado_id']);
        });
    }
};
