<?php

namespace App\Http\Modules\servicios\controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\servicios\request\crearServicioRequest;
use App\Http\Modules\servicios\request\crearServicioRealizadoRequest;
use App\Http\Modules\servicios\request\crearServiciosMultiplesRequest;
use App\Http\Modules\servicios\request\crearIngresoAdicionalRequest;
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
            $data = $crearServicioRequest->validated();
            
            // Si el usuario es admin y proporcionó entidad_id, usarla; si no, usar la del usuario
            if (auth()->user()->esAdmin() && isset($data['entidad_id'])) {
                $data['entidad_id'] = $data['entidad_id'];
            } else {
                $data['entidad_id'] = auth()->user()->obtenerEntidadId();
            }
            
            $servicio = $this->serviciosService->crearServicio($data);
            return response()->json($servicio, 201);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function listarServicio()
    {
        try {
            $user = auth()->user();
            $entidadId = $user->obtenerEntidadId();
            $isAdmin = $user->esAdmin();
            $servicio = $this->serviciosService->listarServicio($entidadId, $isAdmin);
            return response()->json($servicio, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function modificarServicio(Request $data, int $id)
    {
        try {
            $entidadId = auth()->user()->obtenerEntidadId();
            $servicio = $this->serviciosService->modificarServicio($data->only([
                'nombre',
                'estado',
                'precio',
                'porcentaje_pago_empleado'
            ]), $id, $entidadId);
            return response()->json($servicio, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function servicioRealizado(crearServicioRealizadoRequest $request)
    {
        try {
            $servicio = $this->serviciosService->servicioRealizado($request->validated());
            return response()->json($servicio, 201);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function serviciosMultiples(crearServiciosMultiplesRequest $request)
    {
        try {
            $entidadId = auth()->user()->obtenerEntidadId();
            $resultado = $this->serviciosService->serviciosMultiples($request->validated(), $entidadId);
            return response()->json($resultado, 201);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function listarServiciosRealizados(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $empleadoId = $request->get('empleado_id');
            $entidadId = auth()->user()->obtenerEntidadId();
            
            $servicio = $this->serviciosService->listarServiciosRealizados($page, $perPage, $search, $empleadoId, $entidadId);
            return response()->json($servicio, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function calcularPagosEmpleados()
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            $pagos = $this->serviciosService->calcularPagosEmpleados($entidadId);
            return response()->json($pagos, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function totalGanadoServicios()
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            $total = $this->serviciosService->totalGanadoServicios($entidadId);
            return response()->json(['total_ganado' => $total], 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function calcularPagosEmpleadosCompleto()
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            $pagos = $this->serviciosService->calcularPagosEmpleadosCompleto($entidadId);
            return response()->json($pagos, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function calcularGananciaNeta()
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            $ganancia = $this->serviciosService->calcularGananciaNeta($entidadId);
            return response()->json($ganancia, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function gananciasPorMetodoPago()
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            $ganancias = $this->serviciosService->gananciasPorMetodoPago($entidadId);
            return response()->json($ganancias, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function totalGananciasSeparadas()
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            $totales = $this->serviciosService->totalGananciasSeparadas($entidadId);
            return response()->json($totales, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    // Métodos para Ingresos Adicionales
    public function crearIngresoAdicional(crearIngresoAdicionalRequest $request)
    {
        try {
            $entidadId = auth()->user()->obtenerEntidadId();
            $ingreso = $this->serviciosService->crearIngresoAdicional($request->validated(), $entidadId);
            return response()->json($ingreso, 201);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function listarIngresosAdicionales()
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            $ingresos = $this->serviciosService->listarIngresosAdicionales($entidadId);
            return response()->json($ingresos, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function totalIngresosAdicionales()
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            $totales = $this->serviciosService->totalIngresosAdicionales($entidadId);
            return response()->json($totales, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function estadisticasCompletas()
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            $estadisticas = $this->serviciosService->estadisticasCompletas($entidadId);
            return response()->json($estadisticas, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function eliminarIngresoAdicional($id)
    {
        try {
            $resultado = $this->serviciosService->eliminarIngresoAdicional($id);
            return response()->json($resultado, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function eliminarServicioRealizado($id)
    {
        try {
            $resultado = $this->serviciosService->eliminarServicioRealizado($id);
            return response()->json($resultado, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function eliminarServicio($id)
    {
        try {
            $resultado = $this->serviciosService->eliminarServicio($id);
            return response()->json($resultado, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function gananciasDiarias(Request $request)
    {
        try {
            $fecha = $request->get('fecha', date('Y-m-d'));
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            $ganancias = $this->serviciosService->gananciasDiarias($fecha, $entidadId);
            return response()->json($ganancias, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function gananciasPorRango(Request $request)
    {
        try {
            $fechaInicio = $request->get('fecha_inicio', date('Y-m-01'));
            $fechaFin = $request->get('fecha_fin', date('Y-m-d'));
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            $ganancias = $this->serviciosService->gananciasPorRango($fechaInicio, $fechaFin, $entidadId);
            return response()->json($ganancias, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
