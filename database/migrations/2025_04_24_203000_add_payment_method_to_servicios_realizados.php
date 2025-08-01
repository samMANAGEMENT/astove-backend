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
        Schema::table('servicios_realizados', function (Blueprint $table) {
            $table->enum('metodo_pago', ['efectivo', 'transferencia'])->default('efectivo')->after('fecha');
            $table->decimal('monto_efectivo', 10, 2)->default(0)->after('metodo_pago');
            $table->decimal('monto_transferencia', 10, 2)->default(0)->after('monto_efectivo');
            $table->decimal('total_servicio', 10, 2)->after('monto_transferencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicios_realizados', function (Blueprint $table) {
            $table->dropColumn(['metodo_pago', 'monto_efectivo', 'monto_transferencia', 'total_servicio']);
        });
    }
}; 