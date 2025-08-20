<?php

namespace App\Http\Modules\Pagos\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\pagos\request\crearPagoRequest;
use App\Http\Modules\Pagos\Service\PagosService;
use Illuminate\Http\Request;

class PagosController extends Controller 
{
    public function __construct(private pagosService $pagosService)
    {
    }

    public function crearPago(crearPagoRequest $crearPagoRequest){
        try {
            $crearPago = $this->pagosService->crearPago($crearPagoRequest->validated());
            return response()->json($crearPago, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    public function listarPago(Request $request){
        try {
            $entidadId = auth()->user()->obtenerEntidadId();
            $crearPago = $this->pagosService->listarPago($entidadId);
            return response($crearPago, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'error'], 500);
        }
    }

    public function getPagosEmpleadosCompleto()
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            $pagos = $this->pagosService->getPagosEmpleadosCompleto($entidadId);
            return response()->json($pagos, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getGananciaNeta()
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            $ganancia = $this->pagosService->getGananciaNeta($entidadId);
            return response()->json($ganancia, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function crearPagoSemanal(Request $request)
    {
        try {
            $request->validate([
                'empleado_id' => 'required|integer|exists:operadores,id',
                'monto' => 'required|numeric|min:0',
                'tipo_pago' => 'required|in:total,parcial',
                'servicios_incluidos' => 'nullable|array'
            ]);

            $pago = $this->pagosService->crearPagoSemanal(
                $request->empleado_id,
                $request->monto,
                $request->tipo_pago,
                $request->servicios_incluidos
            );

            return response()->json($pago, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getServiciosPendientesEmpleado($empleadoId)
    {
        try {
            $servicios = $this->pagosService->getServiciosPendientesEmpleado($empleadoId);
            return response()->json($servicios, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getServiciosEmpleado($empleadoId)
    {
        try {
            $servicios = $this->pagosService->getServiciosEmpleado($empleadoId);
            return response()->json($servicios, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }



    public function getEstadoPagosEmpleados()
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            $estado = $this->pagosService->getEstadoPagosEmpleados($entidadId);
            return response()->json($estado, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function eliminarPago($id)
    {
        try {
            $resultado = $this->pagosService->eliminarPago($id);
            return response()->json($resultado, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
