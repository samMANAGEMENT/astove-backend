<?php

namespace App\Http\Modules\Agenda\service;

use App\Http\Modules\Agenda\Models\Agenda;
use App\Http\Modules\Agenda\Models\Horario;
use App\Http\Modules\Agenda\Request\crearAgendaRequest;
use App\Http\Modules\Agenda\Request\crearHorarioRequest;
use Illuminate\Support\Facades\DB;

class AgendaService
{
    public function crearAgenda(crearAgendaRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $agenda = Agenda::create($request->validated());
            return $agenda->load('operador');
        });
    }

    public function listarAgendas($entidadId)
    {
        return Agenda::select(['id','operador_id','nombre','descripcion','activa'])
            ->with(['operador' => function($q){
                $q->select('id','nombre','apellido');
            }])
            ->withCount(['horariosActivos as horarios_count'])
            ->whereHas('operador', function ($query) use ($entidadId) {
                $query->where('entidad_id', $entidadId);
            })
            ->get();
    }

    public function obtenerAgenda($id)
    {
        return Agenda::select(['id','operador_id','nombre','descripcion','activa'])
            ->with(['operador' => function($q){
                $q->select('id','nombre','apellido');
            }])
            ->findOrFail($id);
    }

    public function modificarAgenda($id, array $data)
    {
        $agenda = Agenda::findOrFail($id);
        $agenda->update($data);
        return $agenda->load('operador');
    }

    public function eliminarAgenda($id)
    {
        $agenda = Agenda::findOrFail($id);
        $agenda->delete();
        return ['message' => 'Agenda eliminada correctamente'];
    }

    public function crearHorario(crearHorarioRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $horario = Horario::create($request->validated());
            return $horario->load('agenda');
        });
    }

    public function modificarHorario($id, array $data)
    {
        $horario = Horario::findOrFail($id);
        $horario->update($data);
        return $horario->load('agenda');
    }

    public function eliminarHorario($id)
    {
        $horario = Horario::findOrFail($id);
        $horario->delete();
        return ['message' => 'Horario eliminado correctamente'];
    }

    public function obtenerHorariosPorAgenda($agendaId)
    {
        return Horario::select(['id','agenda_id','titulo','hora_inicio','hora_fin','dia_semana','color','notas','activo'])
            ->where('agenda_id', $agendaId)
            ->where('activo', true)
            ->get();
    }

    public function consultarEspaciosDisponibles($agendaId, $fecha = null)
    {
        // Si no se proporciona fecha, usar la fecha actual
        if (!$fecha) {
            $fecha = now()->format('Y-m-d');
        }

        // Obtener la agenda con sus horarios activos
        $agenda = Agenda::with(['operador', 'horariosActivos'])
            ->findOrFail($agendaId);

        // Obtener el día de la semana de la fecha consultada
        $diaSemana = strtolower(now()->parse($fecha)->format('l'));
        $diasSemana = [
            'monday' => 'lunes',
            'tuesday' => 'martes', 
            'wednesday' => 'miercoles',
            'thursday' => 'jueves',
            'friday' => 'viernes',
            'saturday' => 'sabado',
            'sunday' => 'domingo'
        ];
        $diaSemanaEspanol = $diasSemana[$diaSemana] ?? 'lunes';

        // Filtrar horarios para el día específico
        $horariosDelDia = $agenda->horariosActivos()
            ->where('dia_semana', $diaSemanaEspanol)
            ->orderBy('hora_inicio')
            ->get();

        // Obtener citas existentes para esta agenda y fecha
        $citasDelDia = \App\Http\Modules\Agenda\Models\Cita::where('agenda_id', $agenda->id)
            ->where('fecha', $fecha)
            ->get();

        // Marcar disponibilidad real por horario (capacidad 1 por defecto)
        $espaciosDisponibles = $horariosDelDia->map(function ($horario) use ($citasDelDia) {
            $citaExistente = $citasDelDia->where('horario_id', $horario->id)->first();
            return [
                'id' => $horario->id,
                'titulo' => $horario->titulo,
                'hora_inicio' => $horario->hora_inicio,
                'hora_fin' => $horario->hora_fin,
                'color' => $horario->color,
                'notas' => $horario->notas,
                'disponible' => !$citaExistente,
                'cita_existente' => $citaExistente ? [
                    'id' => $citaExistente->id,
                    'cliente_nombre' => $citaExistente->cliente_nombre,
                    'servicio' => $citaExistente->servicio,
                    'estado' => $citaExistente->estado
                ] : null,
                'capacidad' => 1,
                'ocupados' => $citaExistente ? 1 : 0,
                'disponibles' => $citaExistente ? 0 : 1
            ];
        });

        return [
            'agenda' => [
                'id' => $agenda->id,
                'nombre' => $agenda->nombre,
                'descripcion' => $agenda->descripcion,
                'operador' => $agenda->operador ? [
                    'id' => $agenda->operador->id,
                    'nombre' => $agenda->operador->nombre,
                    'apellido' => $agenda->operador->apellido
                ] : null
            ],
            'fecha_consultada' => $fecha,
            'dia_semana' => $diaSemanaEspanol,
            'horarios_disponibles' => $espaciosDisponibles,
            'total_horarios' => $espaciosDisponibles->count(),
            'horarios_con_espacio' => $espaciosDisponibles->where('disponible', true)->count(),
            'horarios_ocupados' => $espaciosDisponibles->where('disponible', false)->count()
        ];
    }

    public function obtenerCalendarioAgenda($agendaId, $mes = null, $anio = null)
    {
        // Si no se proporciona mes y año, usar el actual
        if (!$mes) $mes = now()->month;
        if (!$anio) $anio = now()->year;

        $agenda = Agenda::with(['operador', 'horariosActivos'])->findOrFail($agendaId);
        
        // Obtener todas las citas del mes
        $citas = \App\Http\Modules\Agenda\Models\Cita::where('agenda_id', $agendaId)
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->get();

        // Generar calendario del mes
        $primerDia = \Carbon\Carbon::create($anio, $mes, 1);
        $ultimoDia = $primerDia->copy()->endOfMonth();
        $diasEnMes = $ultimoDia->day;
        
        $calendario = [];
        $diasSemana = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        
        for ($dia = 1; $dia <= $diasEnMes; $dia++) {
            $fecha = \Carbon\Carbon::create($anio, $mes, $dia);
            $diaSemana = $diasSemana[$fecha->dayOfWeek];
            
            // Obtener horarios para este día de la semana
            $horariosDelDia = $agenda->horariosActivos()
                ->where('dia_semana', $diaSemana)
                ->orderBy('hora_inicio')
                ->get();

            // Obtener citas para esta fecha específica (comparación robusta de fecha)
            $citasDelDia = $citas->filter(function($cita) use ($fecha) {
                $citaFecha = $cita->fecha instanceof \Carbon\Carbon
                    ? $cita->fecha->format('Y-m-d')
                    : \Carbon\Carbon::parse($cita->fecha)->format('Y-m-d');
                return $citaFecha === $fecha->format('Y-m-d');
            });

            $calendario[] = [
                'dia' => $dia,
                'fecha' => $fecha->format('Y-m-d'),
                'dia_semana' => $diaSemana,
                'es_hoy' => $fecha->isToday(),
                'es_pasado' => $fecha->isPast(),
                'horarios' => $horariosDelDia->map(function ($horario) use ($citasDelDia) {
                    $citaEnHorario = $citasDelDia->where('horario_id', $horario->id)->first();
                    
                    return [
                        'id' => $horario->id,
                        'titulo' => $horario->titulo,
                        'hora_inicio' => $horario->hora_inicio,
                        'hora_fin' => $horario->hora_fin,
                        'color' => $horario->color,
                        'notas' => $horario->notas,
                        'disponible' => !$citaEnHorario,
                        'cita' => $citaEnHorario ? [
                            'id' => $citaEnHorario->id,
                            'cliente_nombre' => $citaEnHorario->cliente_nombre,
                            'cliente_telefono' => $citaEnHorario->cliente_telefono,
                            'servicio' => $citaEnHorario->servicio,
                            'estado' => $citaEnHorario->estado,
                            'notas' => $citaEnHorario->notas
                        ] : null
                    ];
                })
            ];
        }

        return [
            'agenda' => [
                'id' => $agenda->id,
                'nombre' => $agenda->nombre,
                'descripcion' => $agenda->descripcion,
                'operador' => $agenda->operador ? [
                    'id' => $agenda->operador->id,
                    'nombre' => $agenda->operador->nombre,
                    'apellido' => $agenda->operador->apellido
                ] : null
            ],
            'mes' => $mes,
            'anio' => $anio,
            'nombre_mes' => $primerDia->format('F'),
            'calendario' => $calendario
        ];
    }

    public function crearCita($data)
    {
        return DB::transaction(function () use ($data) {
            // Verificar que el horario esté disponible
            $citaExistente = \App\Http\Modules\Agenda\Models\Cita::where('agenda_id', $data['agenda_id'])
                ->where('horario_id', $data['horario_id'])
                ->where('fecha', $data['fecha'])
                ->first();

            if ($citaExistente) {
                throw new \Exception('Este horario ya está ocupado para la fecha seleccionada');
            }

            $data['created_by'] = auth()->id();
            $cita = \App\Http\Modules\Agenda\Models\Cita::create($data);
            
            return $cita->load(['agenda', 'horario']);
        });
    }

    public function actualizarCita($id, $data)
    {
        $cita = \App\Http\Modules\Agenda\Models\Cita::findOrFail($id);
        $cita->update($data);
        return $cita->load(['agenda', 'horario']);
    }

    public function eliminarCita($id)
    {
        $cita = \App\Http\Modules\Agenda\Models\Cita::findOrFail($id);
        $cita->delete();
        return ['message' => 'Cita eliminada correctamente'];
    }

    public function obtenerDisponibilidadTiempoReal($entidadId, $fecha = null)
    {
        // Si no se proporciona fecha, usar la fecha actual
        if (!$fecha) {
            $fecha = now()->format('Y-m-d');
        }

        // Obtener el día de la semana
        $diaSemana = strtolower(now()->parse($fecha)->format('l'));
        $diasSemana = [
            'monday' => 'lunes',
            'tuesday' => 'martes', 
            'wednesday' => 'miercoles',
            'thursday' => 'jueves',
            'friday' => 'viernes',
            'saturday' => 'sabado',
            'sunday' => 'domingo'
        ];
        $diaSemanaEspanol = $diasSemana[$diaSemana] ?? 'lunes';

        // Obtener todas las agendas de la entidad con sus operadores y horarios
        $agendas = Agenda::select(['id','operador_id','nombre','activa'])
            ->with(['operador' => function($q){ $q->select('id','nombre','apellido'); }])
            ->withCount(['horariosActivos as total_horarios_activos'])
            ->whereHas('operador', function ($query) use ($entidadId) {
                $query->where('entidad_id', $entidadId);
            })
            ->where('activa', true)
            ->get();

        $disponibilidad = [];

        foreach ($agendas as $agenda) {
            // Obtener horarios para el día específico
            $horariosDelDia = $agenda->horariosActivos()
                ->where('dia_semana', $diaSemanaEspanol)
                ->orderBy('hora_inicio')
                ->get();

            // Obtener citas existentes para esta fecha
            $citasDelDia = \App\Http\Modules\Agenda\Models\Cita::where('agenda_id', $agenda->id)
                ->where('fecha', $fecha)
                ->get();

            $horariosDisponibles = $horariosDelDia->map(function ($horario) use ($citasDelDia) {
                $citaExistente = $citasDelDia->where('horario_id', $horario->id)->first();
                
                return [
                    'id' => $horario->id,
                    'titulo' => $horario->titulo,
                    'hora_inicio' => $horario->hora_inicio,
                    'hora_fin' => $horario->hora_fin,
                    'color' => $horario->color,
                    'notas' => $horario->notas,
                    'disponible' => !$citaExistente,
                    'cita_existente' => $citaExistente ? [
                        'id' => $citaExistente->id,
                        'cliente_nombre' => $citaExistente->cliente_nombre,
                        'servicio' => $citaExistente->servicio,
                        'estado' => $citaExistente->estado
                    ] : null
                ];
            });

            $disponibilidad[] = [
                'agenda_id' => $agenda->id,
                'agenda_nombre' => $agenda->nombre,
                'operador' => [
                    'id' => $agenda->operador->id,
                    'nombre' => $agenda->operador->nombre,
                    'apellido' => $agenda->operador->apellido
                ],
                'horarios_disponibles' => $horariosDisponibles,
                'total_horarios' => $horariosDisponibles->count(),
                'horarios_libres' => $horariosDisponibles->where('disponible', true)->count(),
                'horarios_ocupados' => $horariosDisponibles->where('disponible', false)->count()
            ];
        }

        return [
            'fecha_consultada' => $fecha,
            'dia_semana' => $diaSemanaEspanol,
            'disponibilidad' => $disponibilidad,
            'total_agendas' => count($disponibilidad),
            'total_espacios_libres' => collect($disponibilidad)->sum('horarios_libres'),
            'total_espacios_ocupados' => collect($disponibilidad)->sum('horarios_ocupados')
        ];
    }
}
