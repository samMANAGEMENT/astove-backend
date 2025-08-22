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
        return Agenda::with(['operador', 'horariosActivos'])
            ->whereHas('operador', function ($query) use ($entidadId) {
                $query->where('entidad_id', $entidadId);
            })
            ->get();
    }

    public function obtenerAgenda($id)
    {
        return Agenda::with(['operador', 'horariosActivos'])->findOrFail($id);
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
        return Horario::where('agenda_id', $agendaId)
            ->where('activo', true)
            ->get();
    }
}
