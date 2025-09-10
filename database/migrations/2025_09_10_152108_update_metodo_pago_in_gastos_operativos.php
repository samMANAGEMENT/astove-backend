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
        Schema::table('gastos_operativos', function (Blueprint $table) {
            $table->string('metodo_pago', 100)->after('fecha')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gastos_operativos', function (Blueprint $table) {
            $table->dropColumn('metodo_pago');
        });
    }
};
