<?php

namespace App\Http\Modules\servicios\controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\servicios\request\crearServicioRequest;
use Illuminate\Http\Request;
use App\Http\Modules\servicios\service\ServiciosService;

class ServiciosController extends Controller
{
    public function __construct(private ServiciosService $serviciosService)
    {
        
    }

    public function crearServicio(crearServicioRequest $crearServicioRequest)
    {
        try {
            $servicio = $this->serviciosService->crearServicio($crearServicioRequest->validated());
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
