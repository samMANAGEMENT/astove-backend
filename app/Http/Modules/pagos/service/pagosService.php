<?php

namespace App\http\modules\pagos\service;

use App\Http\Modules\pagos\models\pagos;
use App\Http\Modules\servicios\models\ServiciosRealizados;
use App\Http\Modules\Operadores\models\Operadores;
use Illuminate\Support\Facades\DB;

class pagosService
{
    public function crearPago($data){
        return pagos::create($data);
    }

    public function listarPago(){
        return pagos::with('empleado:id,nombre,apellido')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($pago) {
                return [
                    'id' => $pago->id,
                    'empleado_id' => $pago->empleado_id,
                    'empleado' => [
                        'id' => $pago->empleado->id,
                        'nombre' => $pago->empleado->nombre,
                        'apellido' => $pago->empleado->apellido,
                    ],
                    'monto' => $pago->monto,
                    'fecha' => $pago->fecha,
                    'estado' => $pago->estado,
                    'tipo_pago' => $pago->tipo_pago ?? 'total',
                    'monto_pendiente_antes' => $pago->monto_pendiente_antes ?? 0,
                    'monto_pendiente_despues' => $pago->monto_pendiente_despues ?? 0,
                    'semana_pago' => $pago->semana_pago ?? null,
                    'created_at' => $pago->created_at
                ];
            });
    }

    public function getPagosEmpleadosCompleto()
    {
        // Obtener todos los empleados con servicios no pagados
        $empleados = Operadores::with(['serviciosRealizados' => function($query) {
            $query->where('pagado', false)
                  ->with('servicio:id,nombre,porcentaje_pago_empleado,precio');
        }])->get();

        $resultado = [];
        
        foreach ($empleados as $empleado) {
            $totalBruto = 0;
            $totalPagar = 0;
            $detallesServicios = [];
            
            foreach ($empleado->serviciosRealizados as $servicio) {
                $montoServicio = $servicio->servicio->precio * $servicio->cantidad;
                $porcentajeEmpleado = $servicio->servicio->porcentaje_pago_empleado;
                $montoEmpleado = ($montoServicio * $porcentajeEmpleado) / 100;
                
                $totalBruto += $montoServicio;
                $totalPagar += $montoEmpleado;
                
                $detallesServicios[] = [
                    'servicio_id' => $servicio->servicio_id,
                    'servicio_nombre' => $servicio->servicio->nombre,
                    'cantidad' => $servicio->cantidad,
                    'porcentaje_empleado' => $porcentajeEmpleado,
                    'monto_servicio' => $montoServicio,
                    'monto_empleado' => $montoEmpleado
                ];
            }
            
            if ($totalPagar > 0) {
                $resultado[] = [
                    'empleado_id' => $empleado->id,
                    'nombre' => $empleado->nombre,
                    'apellido' => $empleado->apellido,
                    'total_bruto' => $totalBruto,
                    'total_pagar' => $totalPagar,
                    'cantidad_servicios' => count($empleado->serviciosRealizados),
                    'detalles_servicios' => $detallesServicios
                ];
            }
        }
        
        return $resultado;
    }

    public function getEstadoPagosEmpleados()
    {
        // Obtener todos los empleados que tienen servicios realizados
        $empleados = \App\Http\Modules\Operadores\models\Operadores::with(['serviciosRealizados' => function($query) {
            $query->with('servicio:id,nombre,porcentaje_pago_empleado,precio');
        }])->get();

        $resultado = [];

        foreach ($empleados as $empleado) {
            // Calcular total de servicios y pagos
            $totalBruto = 0;
            $totalPagar = 0;
            $totalPendiente = 0;
            $detallesServicios = [];
            
            foreach ($empleado->serviciosRealizados as $servicio) {
                $montoServicio = $servicio->servicio->precio * $servicio->cantidad;
                $porcentajeEmpleado = $servicio->servicio->porcentaje_pago_empleado;
                $montoEmpleado = ($montoServicio * $porcentajeEmpleado) / 100;
                
                $totalBruto += $montoServicio;
                $totalPagar += $montoEmpleado;
                
                // Solo contar como pendiente si no está pagado
                if (!$servicio->pagado) {
                    $totalPendiente += $montoEmpleado;
                }
                
                $detallesServicios[] = [
                    'servicio_id' => $servicio->servicio_id,
                    'servicio_nombre' => $servicio->servicio->nombre,
                    'cantidad' => $servicio->cantidad,
                    'porcentaje_empleado' => $porcentajeEmpleado,
                    'monto_servicio' => $montoServicio,
                    'monto_empleado' => $montoEmpleado,
                    'pagado' => $servicio->pagado
                ];
            }
            
            // Obtener pagos realizados a este empleado
            $pagosRealizados = pagos::where('empleado_id', $empleado->id)->sum('monto');
            
            $saldoPendiente = $totalPendiente; // Usar el total pendiente calculado
            $estadoPago = $saldoPendiente <= 0 ? 'pagado' : ($pagosRealizados > 0 ? 'parcial' : 'pendiente');
            
            // Solo incluir empleados que tienen servicios o pagos
            if ($totalPagar > 0 || $pagosRealizados > 0) {
                $resultado[] = [
                    'empleado_id' => $empleado->id,
                    'nombre' => $empleado->nombre,
                    'apellido' => $empleado->apellido,
                    'total_bruto' => $totalBruto,
                    'total_pagar' => $totalPagar,
                    'pagos_realizados' => $pagosRealizados,
                    'saldo_pendiente' => $saldoPendiente,
                    'estado_pago' => $estadoPago,
                    'detalles_servicios' => $detallesServicios,
                    'cantidad_servicios' => count($empleado->serviciosRealizados)
                ];
            }
        }
        
        return $resultado;
    }

    public function getGananciaNeta()
    {
        // Usar la lógica original del servicio de servicios
        $mesActual = date('m');
        $anioActual = date('Y');

        $servicios = ServiciosRealizados::with('servicio')
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->get();

        $ingresosTotales = $servicios->reduce(function ($carry, $item) {
            return $carry + ($item->cantidad * ($item->servicio->precio ?? 0));
        }, 0);

        $totalPagarEmpleados = $servicios->reduce(function ($carry, $item) {
            $precio = $item->servicio->precio ?? 0;
            $porcentaje = $item->servicio->porcentaje_pago_empleado ?? 50;
            return $carry + ($item->cantidad * $precio * ($porcentaje / 100));
        }, 0);

        $gananciaNeta = $ingresosTotales - $totalPagarEmpleados;

        return [
            'ingresos_totales' => $ingresosTotales,
            'total_pagar_empleados' => $totalPagarEmpleados,
            'ganancia_neta' => $gananciaNeta,
            'porcentaje_ganancia' => $ingresosTotales > 0 ? ($gananciaNeta / $ingresosTotales) * 100 : 0,
            'mes' => $mesActual,
            'anio' => $anioActual
        ];
    }

    public function crearPagoSemanal($empleadoId, $monto, $tipoPago = 'total', $serviciosIncluidos = null)
    {
        DB::beginTransaction();
        
        try {
            // Obtener servicios del mes actual del empleado
            $mesActual = date('m');
            $anioActual = date('Y');
            
            $serviciosEmpleado = ServiciosRealizados::where('empleado_id', $empleadoId)
                ->whereYear('fecha', $anioActual)
                ->whereMonth('fecha', $mesActual)
                ->with('servicio:id,nombre,porcentaje_pago_empleado,precio')
                ->get();
            
            $totalPendiente = 0;
            foreach ($serviciosEmpleado as $servicio) {
                $montoServicio = $servicio->servicio->precio * $servicio->cantidad;
                $porcentajeEmpleado = $servicio->servicio->porcentaje_pago_empleado;
                $montoEmpleado = ($montoServicio * $porcentajeEmpleado) / 100;
                $totalPendiente += $montoEmpleado;
            }
            
            // Crear el pago
            $pago = pagos::create([
                'empleado_id' => $empleadoId,
                'monto' => $monto,
                'fecha' => now(),
                'estado' => true,
                'tipo_pago' => $tipoPago,
                'monto_pendiente_antes' => $totalPendiente,
                'monto_pendiente_despues' => $totalPendiente - $monto,
                'servicios_incluidos' => $serviciosIncluidos,
                'semana_pago' => date('Y-W')
            ]);
            
            // Marcar servicios como pagados
            if ($tipoPago === 'total') {
                // Marcar todos los servicios no pagados del empleado como pagados
                ServiciosRealizados::where('empleado_id', $empleadoId)
                    ->where('pagado', false)
                    ->update([
                        'pagado' => true,
                        'pago_id' => $pago->id
                    ]);
            } else {
                // Marcar servicios específicos como pagados
                if ($serviciosIncluidos) {
                    ServiciosRealizados::whereIn('id', $serviciosIncluidos)
                        ->update([
                            'pagado' => true,
                            'pago_id' => $pago->id
                        ]);
                }
            }
            
            DB::commit();
            return $pago;
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getServiciosPendientesEmpleado($empleadoId)
    {
        // Obtener servicios no pagados del empleado
        return ServiciosRealizados::where('empleado_id', $empleadoId)
            ->where('pagado', false)
            ->with('servicio:id,nombre,porcentaje_pago_empleado,precio')
            ->get()
            ->map(function($servicio) {
                $montoServicio = $servicio->servicio->precio * $servicio->cantidad;
                $porcentajeEmpleado = $servicio->servicio->porcentaje_pago_empleado;
                $montoEmpleado = ($montoServicio * $porcentajeEmpleado) / 100;
                
                return [
                    'id' => $servicio->id,
                    'servicio_nombre' => $servicio->servicio->nombre,
                    'cantidad' => $servicio->cantidad,
                    'fecha' => $servicio->fecha,
                    'porcentaje_empleado' => $porcentajeEmpleado,
                    'monto_servicio' => $montoServicio,
                    'monto_empleado' => $montoEmpleado
                ];
            });
    }
}
