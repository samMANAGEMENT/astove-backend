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
        Schema::table('pagos', function (Blueprint $table) {
            $table->enum('tipo_pago', ['total', 'parcial'])->default('total')->after('estado');
            $table->bigInteger('monto_pendiente_antes')->default(0)->after('tipo_pago');
            $table->bigInteger('monto_pendiente_despues')->default(0)->after('monto_pendiente_antes');
            $table->json('servicios_incluidos')->nullable()->after('monto_pendiente_despues');
            $table->string('semana_pago')->nullable()->after('servicios_incluidos');
        });

        Schema::table('servicios_realizados', function (Blueprint $table) {
            $table->boolean('pagado')->default(false)->after('fecha');
            $table->foreignId('pago_id')->nullable()->constrained('pagos')->after('pagado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicios_realizados', function (Blueprint $table) {
            $table->dropForeign(['pago_id']);
            $table->dropColumn(['pagado', 'pago_id']);
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['tipo_pago', 'monto_pendiente_antes', 'monto_pendiente_despues', 'servicios_incluidos', 'semana_pago']);
        });
    }
}; 