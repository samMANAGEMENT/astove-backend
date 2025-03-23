<?php

namespace App\modules\entidades\controller;

use App\Modules\entidades\service\entidadesService;
use Illuminate\Http\Request;

class entidadesController 
{

    public function __construct(protected entidadesService $entidadesService)
    {
    }

    public function crearEntidad(Request $data){
        try {
            $entidad = $this->entidadesService->crearEntidad($data->all());
            return response()->json(['message' => $entidad], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function listarEntidades(){
        try {
            $entidades = $this->entidadesService->listarEntidades();
            return response()->json(['message' => $entidades], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
