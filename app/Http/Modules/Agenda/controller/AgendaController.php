<?php

namespace App\Http\Modules\Agenda\controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Agenda\service\AgendaService;
use App\Http\Modules\Agenda\Request\crearAgendaRequest;
use App\Http\Modules\Agenda\Request\crearHorarioRequest;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function __construct(private AgendaService $agendaService)
    {
        //
    }

    public function crearAgenda(crearAgendaRequest $request)
    {
        try {
            $agenda = $this->agendaService->crearAgenda($request);
            return response()->json($agenda, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function listarAgendas()
    {
        try {
            $entidadId = auth()->user()->obtenerEntidadId();
            $agendas = $this->agendaService->listarAgendas($entidadId);
            return response()->json($agendas, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function obtenerAgenda($id)
    {
        try {
            $agenda = $this->agendaService->obtenerAgenda($id);
            return response()->json($agenda, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function modificarAgenda(Request $request, $id)
    {
        try {
            $agenda = $this->agendaService->modificarAgenda($id, $request->all());
            return response()->json($agenda, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function eliminarAgenda($id)
    {
        try {
            $result = $this->agendaService->eliminarAgenda($id);
            return response()->json($result, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function crearHorario(crearHorarioRequest $request)
    {
        try {
            $horario = $this->agendaService->crearHorario($request);
            return response()->json($horario, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function modificarHorario(Request $request, $id)
    {
        try {
            $horario = $this->agendaService->modificarHorario($id, $request->all());
            return response()->json($horario, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function eliminarHorario($id)
    {
        try {
            $result = $this->agendaService->eliminarHorario($id);
            return response()->json($result, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function obtenerHorariosPorAgenda($agendaId)
    {
        try {
            $horarios = $this->agendaService->obtenerHorariosPorAgenda($agendaId);
            return response()->json($horarios, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
