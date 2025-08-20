<?php

namespace App\Http\Modules\Operadores\controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Operadores\service\operadoresService;
use Illuminate\Http\Request;

class operadoresController extends Controller
{
    public function __construct(private operadoresService $operadoresService){
        //
    }

    public function crearOperador(Request $request)
    {
        try {
            $operador = $this->operadoresService->crearOperador($request->all());
            return response()->json($operador, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function listarOperadores()
    {
        try {
            $entidadId = auth()->user()->obtenerEntidadId();
            $operadores = $this->operadoresService->listarOperadores($entidadId);
            return response()->json($operadores, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function modificarOperador(Request $request, $id)
    {
        try {
            $operador = $this->operadoresService->modificarOperador($id, $request->all());
            return response()->json($operador, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
