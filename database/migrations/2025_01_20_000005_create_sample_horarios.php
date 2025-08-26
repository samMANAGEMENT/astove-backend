<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Http\Modules\Agenda\Models\Agenda;
use App\Http\Modules\Agenda\Models\Horario;

return new class extends Migration
{
    public function up(): void
    {
        // Obtener todas las agendas
        $agendas = Agenda::all();
        
        // Horarios de ejemplo de 8AM a 8PM
        $horarios = [
            ['hora_inicio' => '08:00', 'hora_fin' => '09:00', 'titulo' => 'Turno Mañana 1', 'color' => '#3B82F6'],
            ['hora_inicio' => '09:00', 'hora_fin' => '10:00', 'titulo' => 'Turno Mañana 2', 'color' => '#10B981'],
            ['hora_inicio' => '10:00', 'hora_fin' => '11:00', 'titulo' => 'Turno Mañana 3', 'color' => '#F59E0B'],
            ['hora_inicio' => '11:00', 'hora_fin' => '12:00', 'titulo' => 'Turno Mañana 4', 'color' => '#EF4444'],
            ['hora_inicio' => '12:00', 'hora_fin' => '13:00', 'titulo' => 'Almuerzo', 'color' => '#8B5CF6'],
            ['hora_inicio' => '13:00', 'hora_fin' => '14:00', 'titulo' => 'Turno Tarde 1', 'color' => '#06B6D4'],
            ['hora_inicio' => '14:00', 'hora_fin' => '15:00', 'titulo' => 'Turno Tarde 2', 'color' => '#84CC16'],
            ['hora_inicio' => '15:00', 'hora_fin' => '16:00', 'titulo' => 'Turno Tarde 3', 'color' => '#F97316'],
            ['hora_inicio' => '16:00', 'hora_fin' => '17:00', 'titulo' => 'Turno Tarde 4', 'color' => '#EC4899'],
            ['hora_inicio' => '17:00', 'hora_fin' => '18:00', 'titulo' => 'Turno Tarde 5', 'color' => '#6366F1'],
            ['hora_inicio' => '18:00', 'hora_fin' => '19:00', 'titulo' => 'Turno Noche 1', 'color' => '#14B8A6'],
            ['hora_inicio' => '19:00', 'hora_fin' => '20:00', 'titulo' => 'Turno Noche 2', 'color' => '#F43F5E'],
        ];

        // Días de la semana (lunes a sábado)
        $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

        foreach ($agendas as $agenda) {
            foreach ($diasSemana as $dia) {
                foreach ($horarios as $horario) {
                    // Verificar si ya existe un horario similar
                    $horarioExistente = Horario::where('agenda_id', $agenda->id)
                        ->where('dia_semana', $dia)
                        ->where('hora_inicio', $horario['hora_inicio'])
                        ->where('hora_fin', $horario['hora_fin'])
                        ->first();

                    if (!$horarioExistente) {
                        Horario::create([
                            'agenda_id' => $agenda->id,
                            'titulo' => $horario['titulo'],
                            'hora_inicio' => $horario['hora_inicio'],
                            'hora_fin' => $horario['hora_fin'],
                            'dia_semana' => $dia,
                            'color' => $horario['color'],
                            'notas' => "Horario automático para {$dia}",
                            'activo' => true
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Eliminar todos los horarios creados por esta migración
        Horario::where('notas', 'like', '%Horario automático%')->delete();
    }
};
