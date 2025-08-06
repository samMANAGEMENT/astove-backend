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
        Schema::create('ingresos_adicionales', function (Blueprint $table) {
            $table->id();
            $table->string('concepto'); // Descripción del ingreso (ej: "Venta de pinzas", "Servicio extra")
            $table->decimal('monto', 10, 2); // Monto del ingreso
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'mixto'])->default('efectivo');
            $table->decimal('monto_efectivo', 10, 2)->default(0);
            $table->decimal('monto_transferencia', 10, 2)->default(0);
            $table->enum('tipo', ['accesorio', 'servicio_ocasional', 'otro'])->default('otro');
            $table->string('categoria')->nullable(); // Categoría opcional (ej: "Herramientas", "Productos")
            $table->text('descripcion')->nullable(); // Descripción adicional opcional
            $table->foreignId('empleado_id')->nullable()->constrained('operadores'); // Quien registró el ingreso
            $table->date('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingresos_adicionales');
    }
}; 