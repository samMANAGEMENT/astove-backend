<?php

namespace App\Http\Modules\servicios\service;

use App\Http\Modules\servicios\models\Servicios;
use App\Http\Modules\servicios\models\ServiciosRealizados;
use App\Http\Modules\servicios\models\IngresosAdicionales;
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

    public function listarServiciosRealizados($page = 1, $perPage = 10, $search = '', $empleadoId = null)
    {
        $query = ServiciosRealizados::with(['empleado:id,nombre,apellido', 'servicio:id,nombre,precio'])
            ->orderBy('created_at', 'desc');

        // Aplicar filtro de búsqueda si se proporciona
        if (!empty($search)) {
            $query->whereHas('servicio', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%");
            })->orWhereHas('empleado', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%");
            });
        }

        // Aplicar filtro por empleado si se proporciona
        if ($empleadoId) {
            $query->where('empleado_id', $empleadoId);
        }

        // Obtener el total de registros para la paginación
        $total = $query->count();

        // Aplicar paginación
        $servicios = $query->skip(($page - 1) * $perPage)
                          ->take($perPage)
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

        return [
            'data' => $servicios,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $total),
            ]
        ];
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
        $totalServicios = $servicios->reduce(function ($carry, $item) {
            return $carry + ($item->total_con_descuento ?? ($item->cantidad * ($item->servicio->precio ?? 0)));
        }, 0);

        // Trae los ingresos adicionales del mes y año actual
        $ingresosAdicionales = IngresosAdicionales::whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->get();

        // Suma el total de ingresos adicionales
        $totalIngresosAdicionales = $ingresosAdicionales->sum('monto');

        // Total general (servicios + ingresos adicionales)
        $total = $totalServicios + $totalIngresosAdicionales;

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

        // Calcular ingresos totales de servicios (con descuento aplicado)
        $ingresosServicios = $servicios->reduce(function ($carry, $item) {
            return $carry + ($item->total_con_descuento ?? ($item->cantidad * ($item->servicio->precio ?? 0)));
        }, 0);

        // Trae los ingresos adicionales del mes y año actual
        $ingresosAdicionales = IngresosAdicionales::whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->get();

        // Suma el total de ingresos adicionales
        $totalIngresosAdicionales = $ingresosAdicionales->sum('monto');

        // Ingresos totales (servicios + ingresos adicionales)
        $ingresosTotales = $ingresosServicios + $totalIngresosAdicionales;

        // Calcular total a pagar a empleados (solo de servicios, no de ingresos adicionales)
        $totalPagarEmpleados = $servicios->reduce(function ($carry, $item) {
            $precio = $item->servicio->precio ?? 0;
            $porcentaje = $item->servicio->porcentaje_pago_empleado ?? 50;
            return $carry + ($item->cantidad * $precio * ($porcentaje / 100));
        }, 0);

        // Calcular ganancia neta (ingresos totales - pagos a empleados)
        // Los ingresos adicionales son 100% ganancia ya que no tienen porcentaje de empleado
        $gananciaNeta = $ingresosTotales - $totalPagarEmpleados;

        // Calcular porcentaje de ganancia
        $porcentajeGanancia = $ingresosTotales > 0 ? ($gananciaNeta / $ingresosTotales) * 100 : 0;

        return [
            'ingresos_totales' => $ingresosTotales,
            'ingresos_servicios' => $ingresosServicios,
            'ingresos_adicionales' => $totalIngresosAdicionales,
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

        // Trae los ingresos adicionales del mes y año actual
        $ingresosAdicionales = IngresosAdicionales::whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->get();

        // Calcular totales por método de pago (servicios + ingresos adicionales)
        $totalEfectivo = $servicios->sum('monto_efectivo') + $ingresosAdicionales->sum('monto_efectivo');
        $totalTransferencia = $servicios->sum('monto_transferencia') + $ingresosAdicionales->sum('monto_transferencia');
        $totalGeneral = $totalEfectivo + $totalTransferencia;

        // Calcular ganancias netas por método de pago (solo de servicios)
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

        // Agregar ganancia de ingresos adicionales (100% ganancia)
        $gananciaEfectivo += $ingresosAdicionales->sum('monto_efectivo');
        $gananciaTransferencia += $ingresosAdicionales->sum('monto_transferencia');

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

        // Trae los ingresos adicionales del mes y año actual
        $ingresosAdicionales = IngresosAdicionales::whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->get();

        // Calcular totales (servicios + ingresos adicionales)
        $totalEfectivo = $servicios->sum('monto_efectivo') + $ingresosAdicionales->sum('monto_efectivo');
        $totalTransferencia = $servicios->sum('monto_transferencia') + $ingresosAdicionales->sum('monto_transferencia');

        return [
            'efectivo' => $totalEfectivo,
            'transferencia' => $totalTransferencia,
            'total' => $totalEfectivo + $totalTransferencia,
            'mes' => $mesActual,
            'anio' => $anioActual
        ];
    }

    // Métodos para Ingresos Adicionales
    public function crearIngresoAdicional(array $data)
    {
        // Validar que los montos sumen el total
        $montoEfectivo = $data['monto_efectivo'] ?? 0;
        $montoTransferencia = $data['monto_transferencia'] ?? 0;
        $montoTotal = $data['monto'] ?? 0;
        
        // Validar que la suma de efectivo y transferencia sea igual al monto total
        $sumaMontos = $montoEfectivo + $montoTransferencia;
        if (abs($sumaMontos - $montoTotal) > 0.01) {
            throw new \Exception('La suma de efectivo y transferencia debe ser igual al monto total');
        }
        
        return IngresosAdicionales::create($data);
    }

    public function listarIngresosAdicionales()
    {
        return IngresosAdicionales::with(['empleado:id,nombre,apellido'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'concepto' => $item->concepto,
                    'monto' => $item->monto,
                    'metodo_pago' => $item->metodo_pago,
                    'monto_efectivo' => $item->monto_efectivo,
                    'monto_transferencia' => $item->monto_transferencia,
                    'tipo' => $item->tipo,
                    'categoria' => $item->categoria,
                    'descripcion' => $item->descripcion,
                    'empleado_id' => $item->empleado_id,
                    'fecha' => $item->fecha,
                    'empleado' => $item->empleado ? [
                        'id' => $item->empleado->id,
                        'nombre' => $item->empleado->nombre,
                        'apellido' => $item->empleado->apellido,
                    ] : null,
                ];
            });
    }

    public function totalIngresosAdicionales()
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae los ingresos adicionales del mes y año actual
        $ingresos = IngresosAdicionales::whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->get();

        // Calcular totales por método de pago
        $totalEfectivo = $ingresos->sum('monto_efectivo');
        $totalTransferencia = $ingresos->sum('monto_transferencia');
        $totalGeneral = $totalEfectivo + $totalTransferencia;

        // Calcular totales por tipo
        $totalAccesorios = $ingresos->where('tipo', 'accesorio')->sum('monto');
        $totalServiciosOcasionales = $ingresos->where('tipo', 'servicio_ocasional')->sum('monto');
        $totalOtros = $ingresos->where('tipo', 'otro')->sum('monto');

        return [
            'efectivo' => $totalEfectivo,
            'transferencia' => $totalTransferencia,
            'total_general' => $totalGeneral,
            'por_tipo' => [
                'accesorios' => $totalAccesorios,
                'servicios_ocasionales' => $totalServiciosOcasionales,
                'otros' => $totalOtros
            ],
            'mes' => $mesActual,
            'anio' => $anioActual
        ];
    }

    public function estadisticasCompletas()
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae los servicios realizados del mes y año actual
        $servicios = ServiciosRealizados::with('servicio')
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->get();

        // Trae los ingresos adicionales del mes y año actual
        $ingresosAdicionales = IngresosAdicionales::whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->get();

        // Calcular ingresos de servicios
        $ingresosServicios = $servicios->reduce(function ($carry, $item) {
            return $carry + ($item->total_con_descuento ?? ($item->cantidad * ($item->servicio->precio ?? 0)));
        }, 0);

        // Calcular ingresos adicionales
        $ingresosAdicionalesTotal = $ingresosAdicionales->sum('monto');

        // Ingresos totales
        $ingresosTotales = $ingresosServicios + $ingresosAdicionalesTotal;

        // Calcular pagos a empleados (solo de servicios)
        $totalPagarEmpleados = $servicios->reduce(function ($carry, $item) {
            $precio = $item->servicio->precio ?? 0;
            $porcentaje = $item->servicio->porcentaje_pago_empleado ?? 50;
            return $carry + ($item->cantidad * $precio * ($porcentaje / 100));
        }, 0);

        // Ganancia neta
        $gananciaNeta = $ingresosTotales - $totalPagarEmpleados;

        // Métodos de pago
        $efectivoServicios = $servicios->sum('monto_efectivo');
        $transferenciaServicios = $servicios->sum('monto_transferencia');
        $efectivoAdicionales = $ingresosAdicionales->sum('monto_efectivo');
        $transferenciaAdicionales = $ingresosAdicionales->sum('monto_transferencia');

        // Totales por método de pago
        $totalEfectivo = $efectivoServicios + $efectivoAdicionales;
        $totalTransferencia = $transferenciaServicios + $transferenciaAdicionales;

        // Desglose por tipo de ingreso adicional
        $accesorios = $ingresosAdicionales->where('tipo', 'accesorio')->sum('monto');
        $serviciosOcasionales = $ingresosAdicionales->where('tipo', 'servicio_ocasional')->sum('monto');
        $otros = $ingresosAdicionales->where('tipo', 'otro')->sum('monto');

        return [
            'resumen_general' => [
                'ingresos_totales' => $ingresosTotales,
                'ingresos_servicios' => $ingresosServicios,
                'ingresos_adicionales' => $ingresosAdicionalesTotal,
                'total_pagar_empleados' => $totalPagarEmpleados,
                'ganancia_neta' => $gananciaNeta,
                'porcentaje_ganancia' => $ingresosTotales > 0 ? ($gananciaNeta / $ingresosTotales) * 100 : 0
            ],
            'metodos_pago' => [
                'efectivo' => [
                    'total' => $totalEfectivo,
                    'servicios' => $efectivoServicios,
                    'adicionales' => $efectivoAdicionales
                ],
                'transferencia' => [
                    'total' => $totalTransferencia,
                    'servicios' => $transferenciaServicios,
                    'adicionales' => $transferenciaAdicionales
                ]
            ],
            'ingresos_adicionales_detalle' => [
                'accesorios' => $accesorios,
                'servicios_ocasionales' => $serviciosOcasionales,
                'otros' => $otros,
                'total_registros' => $ingresosAdicionales->count()
            ],
            'servicios_detalle' => [
                'total_servicios' => $servicios->count(),
                'cantidad_empleados' => $servicios->unique('empleado_id')->count()
            ],
            'mes' => $mesActual,
            'anio' => $anioActual
        ];
    }

    public function eliminarServicioRealizado(int $id)
    {
        $servicioRealizado = ServiciosRealizados::find($id);
        
        if (!$servicioRealizado) {
            throw new \Exception('Servicio realizado no encontrado');
        }

        // Verificar si el servicio ya está pagado
        if ($servicioRealizado->pagado) {
            throw new \Exception('No se puede eliminar un servicio que ya ha sido pagado');
        }

        // Eliminar el servicio realizado
        $servicioRealizado->delete();

        return [
            'message' => 'Servicio realizado eliminado correctamente',
            'id' => $id
        ];
    }

    public function gananciasDiarias($fecha)
    {
        // Trae los servicios realizados de la fecha específica
        $servicios = ServiciosRealizados::with('servicio', 'empleado')
            ->whereDate('fecha', $fecha)
            ->get();

        // Trae los ingresos adicionales de la fecha específica
        $ingresosAdicionales = IngresosAdicionales::whereDate('fecha', $fecha)
            ->get();

        // Calcular ingresos de servicios
        $ingresosServicios = $servicios->reduce(function ($carry, $item) {
            return $carry + ($item->total_con_descuento ?? ($item->cantidad * ($item->servicio->precio ?? 0)));
        }, 0);

        // Calcular ingresos adicionales
        $ingresosAdicionalesTotal = $ingresosAdicionales->sum('monto');

        // Ingresos totales del día
        $ingresosTotales = $ingresosServicios + $ingresosAdicionalesTotal;

        // Calcular pagos a empleados (solo de servicios)
        $totalPagarEmpleados = $servicios->reduce(function ($carry, $item) {
            $precio = $item->servicio->precio ?? 0;
            $porcentaje = $item->servicio->porcentaje_pago_empleado ?? 50;
            return $carry + ($item->cantidad * $precio * ($porcentaje / 100));
        }, 0);

        // Ganancia neta del día
        $gananciaNeta = $ingresosTotales - $totalPagarEmpleados;

        // Métodos de pago
        $efectivoServicios = $servicios->sum('monto_efectivo');
        $transferenciaServicios = $servicios->sum('monto_transferencia');
        $efectivoAdicionales = $ingresosAdicionales->sum('monto_efectivo');
        $transferenciaAdicionales = $ingresosAdicionales->sum('monto_transferencia');

        // Totales por método de pago
        $totalEfectivo = $efectivoServicios + $efectivoAdicionales;
        $totalTransferencia = $transferenciaServicios + $transferenciaAdicionales;

        // Desglose por tipo de ingreso adicional
        $accesorios = $ingresosAdicionales->where('tipo', 'accesorio')->sum('monto');
        $serviciosOcasionales = $ingresosAdicionales->where('tipo', 'servicio_ocasional')->sum('monto');
        $otros = $ingresosAdicionales->where('tipo', 'otro')->sum('monto');

        // Detalles de servicios por empleado
        $serviciosPorEmpleado = $servicios->groupBy('empleado_id')->map(function ($items, $empleadoId) {
            $empleado = $items->first()->empleado;
            $totalEmpleado = $items->reduce(function ($carry, $item) {
                $precio = $item->servicio->precio ?? 0;
                $porcentaje = $item->servicio->porcentaje_pago_empleado ?? 50;
                return $carry + ($item->cantidad * $precio * ($porcentaje / 100));
            }, 0);

            return [
                'empleado_id' => $empleadoId,
                'nombre' => $empleado->nombre ?? 'N/A',
                'apellido' => $empleado->apellido ?? 'N/A',
                'cantidad_servicios' => $items->count(),
                'total_pagar' => $totalEmpleado,
                'servicios' => $items->map(function ($item) {
                    return [
                        'servicio_nombre' => $item->servicio->nombre ?? 'N/A',
                        'cantidad' => $item->cantidad,
                        'precio_unitario' => $item->servicio->precio ?? 0,
                        'porcentaje_empleado' => $item->servicio->porcentaje_pago_empleado ?? 50,
                        'total_servicio' => $item->total_con_descuento ?? ($item->cantidad * ($item->servicio->precio ?? 0)),
                        'metodo_pago' => $item->metodo_pago
                    ];
                })
            ];
        })->values();

        return [
            'fecha' => $fecha,
            'resumen_diario' => [
                'ingresos_totales' => $ingresosTotales,
                'ingresos_servicios' => $ingresosServicios,
                'ingresos_adicionales' => $ingresosAdicionalesTotal,
                'total_pagar_empleados' => $totalPagarEmpleados,
                'ganancia_neta' => $gananciaNeta,
                'porcentaje_ganancia' => $ingresosTotales > 0 ? ($gananciaNeta / $ingresosTotales) * 100 : 0
            ],
            'metodos_pago' => [
                'efectivo' => [
                    'total' => $totalEfectivo,
                    'servicios' => $efectivoServicios,
                    'adicionales' => $efectivoAdicionales
                ],
                'transferencia' => [
                    'total' => $totalTransferencia,
                    'servicios' => $transferenciaServicios,
                    'adicionales' => $transferenciaAdicionales
                ]
            ],
            'ingresos_adicionales_detalle' => [
                'accesorios' => $accesorios,
                'servicios_ocasionales' => $serviciosOcasionales,
                'otros' => $otros,
                'total_registros' => $ingresosAdicionales->count()
            ],
            'servicios_por_empleado' => $serviciosPorEmpleado,
            'estadisticas' => [
                'total_servicios' => $servicios->count(),
                'cantidad_empleados' => $servicios->unique('empleado_id')->count(),
                'promedio_por_servicio' => $servicios->count() > 0 ? $ingresosServicios / $servicios->count() : 0
            ]
        ];
    }

    public function gananciasPorRango($fechaInicio, $fechaFin)
    {
        // Trae los servicios realizados en el rango de fechas
        $servicios = ServiciosRealizados::with('servicio')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get();

        // Trae los ingresos adicionales en el rango de fechas
        $ingresosAdicionales = IngresosAdicionales::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get();

        // Agrupar por día
        $gananciasPorDia = collect();
        
        // Generar array de fechas en el rango
        $fechaActual = \Carbon\Carbon::parse($fechaInicio);
        $fechaFinCarbon = \Carbon\Carbon::parse($fechaFin);
        
        while ($fechaActual->lte($fechaFinCarbon)) {
            $fechaStr = $fechaActual->format('Y-m-d');
            
            // Servicios de este día
            $serviciosDia = $servicios->filter(function ($item) use ($fechaStr) {
                return $item->fecha === $fechaStr;
            });
            
            // Ingresos adicionales de este día
            $ingresosDia = $ingresosAdicionales->filter(function ($item) use ($fechaStr) {
                return $item->fecha === $fechaStr;
            });
            
            // Calcular totales del día
            $ingresosServiciosDia = $serviciosDia->reduce(function ($carry, $item) {
                return $carry + ($item->total_con_descuento ?? ($item->cantidad * ($item->servicio->precio ?? 0)));
            }, 0);
            
            $ingresosAdicionalesDia = $ingresosDia->sum('monto');
            $ingresosTotalesDia = $ingresosServiciosDia + $ingresosAdicionalesDia;
            
            $totalPagarEmpleadosDia = $serviciosDia->reduce(function ($carry, $item) {
                $precio = $item->servicio->precio ?? 0;
                $porcentaje = $item->servicio->porcentaje_pago_empleado ?? 50;
                return $carry + ($item->cantidad * $precio * ($porcentaje / 100));
            }, 0);
            
            $gananciaNetaDia = $ingresosTotalesDia - $totalPagarEmpleadosDia;
            
            $gananciasPorDia->push([
                'fecha' => $fechaStr,
                'ingresos_totales' => $ingresosTotalesDia,
                'ingresos_servicios' => $ingresosServiciosDia,
                'ingresos_adicionales' => $ingresosAdicionalesDia,
                'total_pagar_empleados' => $totalPagarEmpleadosDia,
                'ganancia_neta' => $gananciaNetaDia,
                'cantidad_servicios' => $serviciosDia->count(),
                'cantidad_ingresos_adicionales' => $ingresosDia->count()
            ]);
            
            $fechaActual->addDay();
        }
        
        // Calcular totales del rango
        $ingresosTotalesRango = $gananciasPorDia->sum('ingresos_totales');
        $gananciaNetaRango = $gananciasPorDia->sum('ganancia_neta');
        $totalServiciosRango = $gananciasPorDia->sum('cantidad_servicios');
        $totalIngresosAdicionalesRango = $gananciasPorDia->sum('cantidad_ingresos_adicionales');
        
        return [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'resumen_rango' => [
                'ingresos_totales' => $ingresosTotalesRango,
                'ganancia_neta' => $gananciaNetaRango,
                'total_servicios' => $totalServiciosRango,
                'total_ingresos_adicionales' => $totalIngresosAdicionalesRango,
                'promedio_diario' => $gananciasPorDia->count() > 0 ? $ingresosTotalesRango / $gananciasPorDia->count() : 0
            ],
            'ganancias_por_dia' => $gananciasPorDia->toArray()
        ];
    }
}
