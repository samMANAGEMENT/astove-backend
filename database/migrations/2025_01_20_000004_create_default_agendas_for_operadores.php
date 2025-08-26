<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Http\Modules\Operadores\Models\Operadores;
use App\Http\Modules\Agenda\Models\Agenda;

return new class extends Migration
{
    public function up(): void
    {
        // Crear agendas automáticas para todos los operadores que no tengan una
        $operadores = Operadores::all();
        
        foreach ($operadores as $operador) {
            $agendaExistente = Agenda::where('operador_id', $operador->id)->first();
            
            if (!$agendaExistente) {
                Agenda::create([
                    'operador_id' => $operador->id,
                    'nombre' => "Agenda de {$operador->nombre} {$operador->apellido}",
                    'descripcion' => "Agenda automática para {$operador->nombre} {$operador->apellido}",
                    'activa' => true
                ]);
            }
        }
    }

    public function down(): void
    {
        // No es necesario hacer rollback de datos
    }
};
