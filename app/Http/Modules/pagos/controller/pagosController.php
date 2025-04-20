<?php

namespace App\http\modules\pagos\controller;

use App\Http\Controllers\Controller;
use App\http\modules\pagos\service\pagosService;
use Illuminate\Http\Request;

class pagosController extends Controller 
{
    public function __construct(private pagosService $pagosService)
    {
    }

    public function crearPago(Request $data){
        try {
            $crearPago = $this->pagosService->crearPago($data->all());
            return response()->json($crearPago, 200);
        } catch (\Throwable $th) {
            return response()->json('error', 500);
        }
    }
    public function listarPago(){
        try {
            $crearPago = $this->pagosService->listarPago();
            return response($crearPago, 200);
        } catch (\Throwable $th) {
            return response()->json('error', 500);
        }
    }
}
