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
            $table->decimal('descuento_porcentaje', 5, 2)->default(0)->after('total_servicio');
            $table->decimal('monto_descuento', 10, 2)->default(0)->after('descuento_porcentaje');
            $table->decimal('total_con_descuento', 10, 2)->default(0)->after('monto_descuento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicios_realizados', function (Blueprint $table) {
            $table->dropColumn(['descuento_porcentaje', 'monto_descuento', 'total_con_descuento']);
        });
    }
};
