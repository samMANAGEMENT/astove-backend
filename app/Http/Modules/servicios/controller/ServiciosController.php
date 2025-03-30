<?php

namespace App\Http\Modules\servicios\controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Modules\servicios\service\ServiciosService;

class ServiciosController extends Controller
{
    public function __construct(private ServiciosService $serviciosService)
    {
        
    }

    public function crearServicio(Request $data)
    {
        try {
            $servicio = $this->serviciosService->crearServicio($data->all());
            return response()->json($servicio, 201);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function listarServicio()
    {
        try {
            $servicio = $this->serviciosService->listarServicio();
            return response()->json($servicio, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function modificarServicio(Request $data, $id)
    {
        try {
            $servicio = $this->serviciosService->modificarServicio($data->all(), $id);
            return response()->json($servicio, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
