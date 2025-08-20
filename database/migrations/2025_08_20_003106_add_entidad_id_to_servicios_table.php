<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Http\Modules\Entidades\models\Entidades;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            // Primero agregar la columna como nullable
            $table->foreignId('entidad_id')->after('id')->nullable()->constrained('entidades')->onDelete('cascade');
        });

        // Obtener la primera entidad disponible o crear una por defecto
        $entidad = Entidades::first();
        if (!$entidad) {
            $entidad = Entidades::create([
                'nombre' => 'Entidad Por Defecto',
                'direccion' => 'Dirección por defecto',
                'estado' => true
            ]);
        }

        // Actualizar todos los servicios existentes con la entidad por defecto
        DB::table('servicios')->update(['entidad_id' => $entidad->id]);

        // Ahora hacer la columna NOT NULL
        Schema::table('servicios', function (Blueprint $table) {
            $table->foreignId('entidad_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropForeign(['entidad_id']);
            $table->dropColumn('entidad_id');
        });
    }
};
