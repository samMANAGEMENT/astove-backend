<?php

namespace App\Http\Modules\Pagos\Service;

use App\Http\Modules\Pagos\Models\Pagos;
use App\Http\Modules\servicios\models\ServiciosRealizados;
use App\Http\Modules\Operadores\Models\Operadores;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PagosService
{
    public function crearPago($data)
    {
        return Pagos::create($data);
    }

    public function listarPago($userEntityId = null)
    {
        $query = Pagos::with('empleado:id,nombre,apellido,entidad_id');

        // Si se proporciona un ID de entidad, filtrar por esa entidad
        if ($userEntityId) {
            $query->whereHas('empleado', function ($q) use ($userEntityId) {
                $q->where('entidad_id', $userEntityId);
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($pago) {
                return [
                    'id' => $pago->id,
                    'empleado_id' => $pago->empleado_id,
                    'empleado' => [
                        'id' => $pago->empleado->id,
                        'nombre' => $pago->empleado->nombre,
                        'apellido' => $pago->empleado->apellido,
                        'entidad_id' => $pago->empleado->entidad_id,
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

    public function getPagosEmpleadosCompleto($entidadId = null)
    {
        // Obtener todos los empleados con servicios no pagados
        $query = Operadores::with(['serviciosRealizados' => function ($query) {
            $query->whereRaw('pagado IS FALSE')
                ->with('servicio:id,nombre,porcentaje_pago_empleado,precio');
        }]);

        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }

        $empleados = $query->get();

        $resultado = [];

        foreach ($empleados as $empleado) {
            $totalBruto = 0;
            $totalPagar = 0;
            $detallesServicios = [];

            foreach ($empleado->serviciosRealizados as $servicio) {
                $montoServicio = $servicio->total_con_descuento ?? ($servicio->servicio->precio * $servicio->cantidad);
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

    public function getEstadoPagosEmpleados($entidadId = null)
    {
        // Obtener todos los empleados que tienen servicios realizados
        $query = \App\Http\Modules\Operadores\Models\Operadores::with(['serviciosRealizados' => function ($query) {
            $query->with('servicio:id,nombre,porcentaje_pago_empleado,precio');
        }]);

        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }

        $empleados = $query->get();

        $resultado = [];

        foreach ($empleados as $empleado) {
            // Calcular total de servicios y pagos
            $totalBruto = 0;
            $totalPagar = 0;
            $totalPendiente = 0;
            $detallesServicios = [];

            foreach ($empleado->serviciosRealizados as $servicio) {
                $montoServicio = $servicio->total_con_descuento ?? ($servicio->servicio->precio * $servicio->cantidad);
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
            $pagosRealizados = Pagos::where('empleado_id', $empleado->id)->sum('monto');

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

    public function getGananciaNeta($entidadId = null)
    {
        // Usar la lógica original del servicio de servicios
        $mesActual = date('m');
        $anioActual = date('Y');

        $query = ServiciosRealizados::with('servicio')
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual);

        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->whereHas('servicio', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }

        $servicios = $query->get();

        $ingresosTotales = $servicios->reduce(function ($carry, $item) {
            return $carry + ($item->total_con_descuento ?? ($item->cantidad * ($item->servicio->precio ?? 0)));
        }, 0);

        $totalPagarEmpleados = $servicios->reduce(function ($carry, $item) {
            $totalConDescuento = $item->total_con_descuento ?? ($item->cantidad * ($item->servicio->precio ?? 0));
            $porcentaje = $item->servicio->porcentaje_pago_empleado ?? 50;
            return $carry + ($totalConDescuento * ($porcentaje / 100));
        }, 0);

        // Obtener total de gastos del mes
        $user = Auth::user();
        $entidadId = $user->obtenerEntidadId();

        $totalGastos = \App\Http\Modules\Gastos\models\GastosOperativos::where('entidad_id', $entidadId)
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->sum('monto');

        $gananciaNeta = $ingresosTotales - $totalPagarEmpleados - $totalGastos;

        // Calcular datos del mes anterior para comparaciones
        $mesAnterior = $mesActual == 1 ? 12 : $mesActual - 1;
        $anioAnterior = $mesActual == 1 ? $anioActual - 1 : $anioActual;

        // Ingresos del mes anterior
        $queryMesAnterior = ServiciosRealizados::with('servicio')
            ->whereYear('fecha', $anioAnterior)
            ->whereMonth('fecha', $mesAnterior);

        if ($entidadId) {
            $queryMesAnterior->whereHas('servicio', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }

        $serviciosMesAnterior = $queryMesAnterior->get();
        $ingresosTotalesMesAnterior = $serviciosMesAnterior->reduce(function ($carry, $item) {
            return $carry + ($item->total_con_descuento ?? ($item->cantidad * ($item->servicio->precio ?? 0)));
        }, 0);

        // Gastos del mes anterior
        $totalGastosMesAnterior = \App\Http\Modules\Gastos\models\GastosOperativos::where('entidad_id', $entidadId)
            ->whereYear('fecha', $anioAnterior)
            ->whereMonth('fecha', $mesAnterior)
            ->sum('monto');

        return [
            'ingresos_totales' => $ingresosTotales,
            'total_pagar_empleados' => $totalPagarEmpleados,
            'total_gastos' => $totalGastos,
            'ganancia_neta' => $gananciaNeta,
            'porcentaje_ganancia' => $ingresosTotales > 0 ? ($gananciaNeta / $ingresosTotales) * 100 : 0,
            'mes' => $mesActual,
            'anio' => $anioActual,
            // Datos del mes anterior para comparaciones
            'ingresos_totales_mes_anterior' => $ingresosTotalesMesAnterior,
            'gastos_mes_anterior' => $totalGastosMesAnterior
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
                $montoServicio = $servicio->total_con_descuento ?? ($servicio->servicio->precio * $servicio->cantidad);
                $porcentajeEmpleado = $servicio->servicio->porcentaje_pago_empleado;
                $montoEmpleado = ($montoServicio * $porcentajeEmpleado) / 100;
                $totalPendiente += $montoEmpleado;
            }

            // Crear el pago
            $pago = Pagos::create([
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
                ServiciosRealizados::updatePagado($empleadoId, true, $pago->id);
            } else {
                // Marcar servicios específicos como pagados
                if ($serviciosIncluidos) {
                    ServiciosRealizados::updateServiciosPagados($serviciosIncluidos, $pago->id);
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
            ->whereRaw('pagado IS FALSE')
            ->with('servicio:id,nombre,porcentaje_pago_empleado,precio')
            ->get()
            ->map(function ($servicio) {
                $montoServicio = $servicio->total_con_descuento ?? ($servicio->servicio->precio * $servicio->cantidad);
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

    public function getServiciosEmpleado($empleadoId)
    {
        // Obtener todos los servicios del empleado (pagados y pendientes)
        return ServiciosRealizados::where('empleado_id', $empleadoId)
            ->with('servicio:id,nombre,porcentaje_pago_empleado,precio')
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function ($servicio) {
                $montoServicio = $servicio->total_con_descuento ?? ($servicio->servicio->precio * $servicio->cantidad);
                $porcentajeEmpleado = $servicio->servicio->porcentaje_pago_empleado;
                $montoEmpleado = ($montoServicio * $porcentajeEmpleado) / 100;

                return [
                    'id' => $servicio->id,
                    'servicio_nombre' => $servicio->servicio->nombre,
                    'cantidad' => $servicio->cantidad,
                    'fecha' => $servicio->fecha,
                    'porcentaje_empleado' => $porcentajeEmpleado,
                    'monto_servicio' => $montoServicio,
                    'monto_empleado' => $montoEmpleado,
                    'pagado' => $servicio->pagado
                ];
            });
    }

    public function eliminarPago($id)
    {
        $pago = Pagos::find($id);

        if (!$pago) {
            throw new \Exception('El pago no existe');
        }

        // Verificar si el pago está relacionado con servicios realizados
        $serviciosRelacionados = ServiciosRealizados::where('pago_id', $id)->count();

        if ($serviciosRelacionados > 0) {
            throw new \Exception('No se puede eliminar un pago que tiene servicios asociados. Primero debe desmarcar los servicios como pagados.');
        }

        // Eliminar el pago
        $pago->delete();

        return [
            'message' => 'Pago eliminado exitosamente',
            'id' => $id
        ];
    }
}
