<?php

namespace App\Http\Modules\servicios\controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\servicios\request\crearServicioRequest;
use App\Http\Modules\servicios\Request\crearIngresoAdicionalRequest;
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
            $servicio = $this->serviciosService->modificarServicio($data->only([
                'nombre',
                'precio',
                'porcentaje_pago_empleado'
            ]), $id);
            return response()->json($servicio, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function servicioRealizado(Request $request)
    {
        try {
            $servicio = $this->serviciosService->servicioRealizado($request->only([
                'empleado_id',
                'servicio_id',
                'cantidad',
                'fecha',
                'metodo_pago',
                'monto_efectivo',
                'monto_transferencia',
                'total_servicio',
                'descuento_porcentaje'
            ]));
            return response()->json($servicio, 201);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function listarServiciosRealizados()
    {
        try {
            $servicio = $this->serviciosService->listarServiciosRealizados();
            return response()->json($servicio, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function calcularPagosEmpleados()
    {
        try {
            $pagos = $this->serviciosService->calcularPagosEmpleados();
            return response()->json($pagos, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function totalGanadoServicios()
    {
        try {
            $total = $this->serviciosService->totalGanadoServicios();
            return response()->json(['total_ganado' => $total], 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function calcularPagosEmpleadosCompleto()
    {
        try {
            $pagos = $this->serviciosService->calcularPagosEmpleadosCompleto();
            return response()->json($pagos, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function calcularGananciaNeta()
    {
        try {
            $ganancia = $this->serviciosService->calcularGananciaNeta();
            return response()->json($ganancia, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function gananciasPorMetodoPago()
    {
        try {
            $ganancias = $this->serviciosService->gananciasPorMetodoPago();
            return response()->json($ganancias, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function totalGananciasSeparadas()
    {
        try {
            $totales = $this->serviciosService->totalGananciasSeparadas();
            return response()->json($totales, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    // Métodos para Ingresos Adicionales
    public function crearIngresoAdicional(crearIngresoAdicionalRequest $request)
    {
        try {
            $ingreso = $this->serviciosService->crearIngresoAdicional($request->validated());
            return response()->json($ingreso, 201);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function listarIngresosAdicionales()
    {
        try {
            $ingresos = $this->serviciosService->listarIngresosAdicionales();
            return response()->json($ingresos, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function totalIngresosAdicionales()
    {
        try {
            $totales = $this->serviciosService->totalIngresosAdicionales();
            return response()->json($totales, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function estadisticasCompletas()
    {
        try {
            $estadisticas = $this->serviciosService->estadisticasCompletas();
            return response()->json($estadisticas, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
