<?php

namespace App\Http\Modules\Operadores\controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Operadores\service\operadoresService;
use Illuminate\Http\Request;

class operadoresController extends Controller
{
    public function __construct(private operadoresService $operadoresService)
    {}

    public function listarOperadores()
    {
        try {
            $operadores = $this->operadoresService->listarOperadores();
            return response()->json($operadores, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
