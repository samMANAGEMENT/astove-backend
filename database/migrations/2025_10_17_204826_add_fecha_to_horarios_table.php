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
        Schema::table('horarios', function (Blueprint $table) {
            $table->date('fecha')->nullable()->after('dia_semana');
            $table->index(['agenda_id', 'fecha', 'dia_semana']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropIndex(['agenda_id', 'fecha', 'dia_semana']);
            $table->dropColumn('fecha');
        });
    }
};
