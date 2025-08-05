<?php

namespace App\Http\Modules\servicios\service;

use App\Http\Modules\servicios\models\Servicios;
use App\Http\Modules\servicios\models\ServiciosRealizados;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiciosService 
{
    public function crearServicio(array $data)
    {
        return Servicios::create($data);
    }

    public function listarServicio()
    {
        return Servicios::orderBy('id', 'asc')->get();
    }

    public function modificarServicio(array $data, int $id)
    {
        return Servicios::where('id', $id)->update($data);
    }

    public function servicioRealizado(array $data)
    {
        // Validar que los montos sumen el total del servicio
        $montoEfectivo = $data['monto_efectivo'] ?? 0;
        $montoTransferencia = $data['monto_transferencia'] ?? 0;
        $totalServicio = $data['total_servicio'] ?? 0;
        $descuentoPorcentaje = $data['descuento_porcentaje'] ?? 0;
        
        // Si no se proporciona total_servicio, calcularlo
        if ($totalServicio == 0) {
            $servicio = Servicios::find($data['servicio_id']);
            $totalServicio = $data['cantidad'] * ($servicio->precio ?? 0);
        }
        
        // Calcular descuento y total con descuento
        $montoDescuento = $totalServicio * ($descuentoPorcentaje / 100);
        $totalConDescuento = $totalServicio - $montoDescuento;
        
        // Validar que la suma de efectivo y transferencia sea igual al total con descuento
        // Usar tolerancia para problemas de precisión decimal
        $sumaMontos = $montoEfectivo + $montoTransferencia;
        if (abs($sumaMontos - $totalConDescuento) > 0.01) {
            throw new \Exception('La suma de efectivo y transferencia debe ser igual al total del servicio con descuento aplicado');
        }
        
        // Agregar los campos calculados al array de datos
        $data['monto_descuento'] = $montoDescuento;
        $data['total_con_descuento'] = $totalConDescuento;
        
        return ServiciosRealizados::create($data);
    }

    public function listarServiciosRealizados()
    {
        return ServiciosRealizados::with(['empleado:id,nombre,apellido', 'servicio:id,nombre,precio'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'empleado_id' => $item->empleado_id,
                    'servicio_id' => $item->servicio_id,
                    'cantidad' => $item->cantidad,
                    'fecha' => $item->fecha,
                    'metodo_pago' => $item->metodo_pago,
                    'monto_efectivo' => $item->monto_efectivo,
                    'monto_transferencia' => $item->monto_transferencia,
                    'total_servicio' => $item->total_servicio,
                    'descuento_porcentaje' => $item->descuento_porcentaje,
                    'monto_descuento' => $item->monto_descuento,
                    'total_con_descuento' => $item->total_con_descuento,
                    'empleado' => $item->empleado ? [
                        'id' => $item->empleado->id,
                        'nombre' => $item->empleado->nombre,
                        'apellido' => $item->empleado->apellido,
                    ] : null,
                    'servicio' => $item->servicio ? [
                        'id' => $item->servicio->id,
                        'nombre' => $item->servicio->nombre,
                        'precio' => $item->servicio->precio,
                    ] : null,
                ];
            });
    }

    public function calcularPagosEmpleados()
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae solo los servicios realizados del mes y año actual con la relación del servicio (para el precio y porcentaje)
        $servicios = ServiciosRealizados::with('servicio', 'empleado')
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->get();

        // Agrupa y suma por empleado
        $pagos = $servicios->groupBy('empleado_id')->map(function ($items, $empleado_id) {
            $empleado = $items->first()->empleado;
            $total = $items->reduce(function ($carry, $item) {
                // El operador recibe su porcentaje sobre el precio ORIGINAL (sin descuento)
                $precio = $item->servicio->precio ?? 0;
                $porcentaje = $item->servicio->porcentaje_pago_empleado ?? 50;
                return $carry + ($item->cantidad * $precio * ($porcentaje / 100));
            }, 0);

            return [
                'empleado_id' => $empleado_id,
                'nombre' => $empleado->nombre ?? null,
                'apellido' => $empleado->apellido ?? null,
                'total_pagar' => $total
            ];
        })->values();

        return $pagos;
    }

    public function calcularPagosEmpleadosCompleto()
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae solo los servicios realizados del mes y año actual con la relación del servicio
        $servicios = ServiciosRealizados::with('servicio', 'empleado')
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->get();

        // Agrupa y suma por empleado con detalles completos
        $pagos = $servicios->groupBy('empleado_id')->map(function ($items, $empleado_id) {
            $empleado = $items->first()->empleado;
            
            // Calcular total bruto (precio original sin descuento) y total a pagar
            $totalBruto = $items->reduce(function ($carry, $item) {
                return $carry + ($item->cantidad * ($item->servicio->precio ?? 0));
            }, 0);

            $totalPagar = $items->reduce(function ($carry, $item) {
                // El operador recibe su porcentaje sobre el precio ORIGINAL (sin descuento)
                $precio = $item->servicio->precio ?? 0;
                $porcentaje = $item->servicio->porcentaje_pago_empleado ?? 50;
                return $carry + ($item->cantidad * $precio * ($porcentaje / 100));
            }, 0);

            // Detalles de servicios por empleado
            $detallesServicios = $items->map(function ($item) {
                $precio = $item->servicio->precio ?? 0;
                $porcentaje = $item->servicio->porcentaje_pago_empleado ?? 50;
                $subtotal = $item->cantidad * $precio;
                $pagoEmpleado = $subtotal * ($porcentaje / 100);

                return [
                    'servicio_id' => $item->servicio_id,
                    'servicio_nombre' => $item->servicio->nombre ?? 'N/A',
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotal,
                    'porcentaje_empleado' => $porcentaje,
                    'pago_empleado' => $pagoEmpleado,
                    'descuento_porcentaje' => $item->descuento_porcentaje,
                    'monto_descuento' => $item->monto_descuento,
                    'total_con_descuento' => $item->total_con_descuento,
                    'fecha' => $item->fecha
                ];
            });

            return [
                'empleado_id' => $empleado_id,
                'nombre' => $empleado->nombre ?? 'N/A',
                'apellido' => $empleado->apellido ?? 'N/A',
                'total_bruto' => $totalBruto,
                'total_pagar' => $totalPagar,
                'detalles_servicios' => $detallesServicios,
                'cantidad_servicios' => $items->count()
            ];
        })->values();

        return $pagos;
    }

    public function totalGanadoServicios()
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae solo los servicios realizados del mes y año actual
        $servicios = ServiciosRealizados::with('servicio')
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->get();

        // Suma el total con descuento (lo que realmente se cobró)
        $total = $servicios->reduce(function ($carry, $item) {
            return $carry + ($item->total_con_descuento ?? ($item->cantidad * ($item->servicio->precio ?? 0)));
        }, 0);

        return $total;
    }

    public function calcularGananciaNeta()
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae solo los servicios realizados del mes y año actual
        $servicios = ServiciosRealizados::with('servicio')
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->get();

        // Calcular ingresos totales (con descuento aplicado)
        $ingresosTotales = $servicios->reduce(function ($carry, $item) {
            return $carry + ($item->total_con_descuento ?? ($item->cantidad * ($item->servicio->precio ?? 0)));
        }, 0);

        // Calcular total a pagar a empleados (sobre precio original, sin descuento)
        $totalPagarEmpleados = $servicios->reduce(function ($carry, $item) {
            $precio = $item->servicio->precio ?? 0;
            $porcentaje = $item->servicio->porcentaje_pago_empleado ?? 50;
            return $carry + ($item->cantidad * $precio * ($porcentaje / 100));
        }, 0);

        // Calcular ganancia neta
        $gananciaNeta = $ingresosTotales - $totalPagarEmpleados;

        // Calcular porcentaje de ganancia
        $porcentajeGanancia = $ingresosTotales > 0 ? ($gananciaNeta / $ingresosTotales) * 100 : 0;

        return [
            'ingresos_totales' => $ingresosTotales,
            'total_pagar_empleados' => $totalPagarEmpleados,
            'ganancia_neta' => $gananciaNeta,
            'porcentaje_ganancia' => $porcentajeGanancia,
            'mes' => $mesActual,
            'anio' => $anioActual
        ];
    }

    public function gananciasPorMetodoPago()
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae los servicios realizados del mes y año actual
        $servicios = ServiciosRealizados::with('servicio')
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->get();

        // Calcular totales por método de pago
        $totalEfectivo = $servicios->sum('monto_efectivo');
        $totalTransferencia = $servicios->sum('monto_transferencia');
        $totalGeneral = $totalEfectivo + $totalTransferencia;

        // Calcular ganancias netas por método de pago
        $gananciaEfectivo = $servicios->reduce(function ($carry, $item) {
            // El operador recibe su porcentaje sobre el precio ORIGINAL (sin descuento)
            $precio = $item->servicio->precio ?? 0;
            $porcentaje = $item->servicio->porcentaje_pago_empleado ?? 50;
            $ingresoEmpleado = $item->cantidad * $precio * ($porcentaje / 100);
            
            // La ganancia se calcula sobre lo que realmente se cobró (efectivo)
            return $carry + ($item->monto_efectivo - $ingresoEmpleado);
        }, 0);

        $gananciaTransferencia = $servicios->reduce(function ($carry, $item) {
            // El operador recibe su porcentaje sobre el precio ORIGINAL (sin descuento)
            $precio = $item->servicio->precio ?? 0;
            $porcentaje = $item->servicio->porcentaje_pago_empleado ?? 50;
            $ingresoEmpleado = $item->cantidad * $precio * ($porcentaje / 100);
            
            // La ganancia se calcula sobre lo que realmente se cobró (transferencia)
            return $carry + ($item->monto_transferencia - $ingresoEmpleado);
        }, 0);

        return [
            'efectivo' => [
                'total_ingresos' => $totalEfectivo,
                'ganancia_neta' => $gananciaEfectivo,
                'porcentaje_del_total' => $totalGeneral > 0 ? ($totalEfectivo / $totalGeneral) * 100 : 0
            ],
            'transferencia' => [
                'total_ingresos' => $totalTransferencia,
                'ganancia_neta' => $gananciaTransferencia,
                'porcentaje_del_total' => $totalGeneral > 0 ? ($totalTransferencia / $totalGeneral) * 100 : 0
            ],
            'total_general' => $totalGeneral,
            'mes' => $mesActual,
            'anio' => $anioActual
        ];
    }

    public function totalGananciasSeparadas()
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae los servicios realizados del mes y año actual
        $servicios = ServiciosRealizados::with('servicio')
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->get();

        // Calcular totales
        $totalEfectivo = $servicios->sum('monto_efectivo');
        $totalTransferencia = $servicios->sum('monto_transferencia');

        return [
            'efectivo' => $totalEfectivo,
            'transferencia' => $totalTransferencia,
            'total' => $totalEfectivo + $totalTransferencia,
            'mes' => $mesActual,
            'anio' => $anioActual
        ];
    }
}
