<?php

namespace App\Http\Modules\Entidades\controller;

use App\Http\Modules\Entidades\request\crearEntidadesRequest;
use App\Http\Modules\Entidades\service\EntidadesService;
use Illuminate\Http\Request;

class EntidadesController
{
    public function __construct(private EntidadesService $entidadesSerivce) {}

    public function crearEntidad(crearEntidadesRequest $crearEntidadesRequest)
    {
        try {
            $entidad = $this->entidadesSerivce->crearEntidad($crearEntidadesRequest->validated());
            return response()->json($entidad, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function listarEntidad()
    {
        try {
            $entidad = $this->entidadesSerivce->listarEntidad();
            return response()->json($entidad, 200);
        } catch (\Throwable $th) {
            return response()->json(['ocurrio un error al momento de listar las entidades'], 500);
        }
    }

    public function actualizarEntidad(Request $data, int $id)
    {
        try {
            $entidad = $this->entidadesSerivce->actualizarEntidad($data->all(), $id);
            return response()->json($entidad);
        } catch (\Throwable $th) {
            return response()->json(['Ocurrio un error', $th], 500);
        }
    }
}
