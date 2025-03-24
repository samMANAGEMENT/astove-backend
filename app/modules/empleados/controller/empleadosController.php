<?php

namespace App\modules\empleados\controller;

use App\Modules\empleados\service\empleadosService;
use Illuminate\Http\Request;

class empleadosController
{

    public function __construct(protected empleadosService $empleadosService)
    {
    }
    public function crearEmpleado(Request $data){
        try {
            $empleados = $this->empleadosService->crearEmpleado($data->all());
            return response()->json(['message' => $empleados], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

public function listarEmpleados(){
        try{
            $empleados = $this->empleadosService->listarEmpleados();
            return response()->json(['message' => $empleados], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
}
}