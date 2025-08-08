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
        Schema::table('ventas', function (Blueprint $table) {
            // Cambiar el campo total de string a decimal usando USING para PostgreSQL
            DB::statement('ALTER TABLE ventas ALTER COLUMN total TYPE DECIMAL(10,2) USING total::DECIMAL(10,2)');
            
            // Agregar campos adicionales
            $table->decimal('ganancia_total', 10, 2)->default(0)->after('total');
            $table->foreignId('empleado_id')->nullable()->constrained('operadores')->after('ganancia_total');
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'mixto'])->default('efectivo')->after('empleado_id');
            $table->decimal('monto_efectivo', 10, 2)->default(0)->after('metodo_pago');
            $table->decimal('monto_transferencia', 10, 2)->default(0)->after('monto_efectivo');
            $table->text('observaciones')->nullable()->after('monto_transferencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['empleado_id']);
            $table->dropColumn([
                'ganancia_total',
                'empleado_id',
                'metodo_pago',
                'monto_efectivo',
                'monto_transferencia',
                'observaciones'
            ]);
        });
        
        // Revertir el cambio de tipo de total
        DB::statement('ALTER TABLE ventas ALTER COLUMN total TYPE VARCHAR(255) USING total::VARCHAR(255)');
    }
};
