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
        Schema::create('caja_menor', function (Blueprint $table) {
            $table->id(); 
            $table->decimal('monto', 10, 2); // Monto fijo (ej: 1000)
            $table->foreignId('entidad_id')->constrained('entidades')->onDelete('cascade'); // Relación con la entidad
            $table->foreignId('operador_id')->constrained('operadores')->onDelete('cascade'); // Relación con el operador que hizo el movimiento
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->onDelete('set null'); // Relación con el servicio (si aplica)
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'mixto']);
            $table->decimal('monto_efectivo', 10, 2)->default(0);
            $table->decimal('monto_transferencia', 10, 2)->default(0);
            $table->dateTime('fecha'); // Fecha del movimiento
            $table->text('observaciones')->nullable();  // Notas adicionales
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_menor');
    }
};
