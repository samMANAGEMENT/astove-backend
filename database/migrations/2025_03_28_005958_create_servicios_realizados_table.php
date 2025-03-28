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
        Schema::create('servicios_realizados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('operadores');
            $table->foreignId('servicio_id')->constrained('servicios');
            $table->string('cantidad');
            $table->dateTime('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios_realizados');
    }
};
