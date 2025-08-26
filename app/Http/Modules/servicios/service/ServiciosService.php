<?php

namespace App\Http\Modules\servicios\service;

use App\Http\Modules\servicios\models\Servicios;
use App\Http\Modules\servicios\models\ServiciosRealizados;
use App\Http\Modules\servicios\models\IngresosAdicionales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServiciosService 
{
    public function crearServicio(array $data)
    {
        return Servicios::create($data);
    }

    public function listarServicio($entidadId = null, $isAdmin = false)
    {
        $query = Servicios::where('nombre', 'not like', 'Servicio Ocasional%')
            ->orderBy('id', 'asc');
        
        // Si no es admin, filtrar por entidad
        if (!$isAdmin && $entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        return $query->get();
    }

    public function modificarServicio(array $data, int $id, $entidadId = null)
    {
        $query = Servicios::where('id', $id);
        
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        return $query->update($data);
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
        
        // Si la fecha viene solo como Y-m-d, agregar la hora actual
        if (isset($data['fecha']) && !str_contains($data['fecha'], ':')) {
            // Usar Carbon con zona horaria explícita para asegurar la hora correcta
            $horaActual = \Carbon\Carbon::now('America/Bogota')->format('H:i:s');
            $data['fecha'] = $data['fecha'] . ' ' . $horaActual;
            
            // Log para debug
            \Log::info('Servicio realizado - Fecha original: ' . $data['fecha'] . ' - Hora actual: ' . $horaActual);
        }
        
        return ServiciosRealizados::create($data);
    }

    public function listarServiciosRealizados($page = 1, $perPage = 10, $search = '', $empleadoId = null, $entidadId = null)
    {
        $query = ServiciosRealizados::with(['empleado:id,nombre,apellido', 'servicio:id,nombre,precio'])
            ->orderBy('created_at', 'desc');
        
        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->whereHas('servicio', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }

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

    public function calcularPagosEmpleados($entidadId = null)
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae solo los servicios realizados del mes y año actual con la relación del servicio (para el precio y porcentaje)
        $query = ServiciosRealizados::with('servicio', 'empleado')
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual);
        
        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->whereHas('servicio', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $servicios = $query->get();

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

    public function calcularPagosEmpleadosCompleto($entidadId = null)
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae solo los servicios realizados del mes y año actual con la relación del servicio
        $query = ServiciosRealizados::with('servicio', 'empleado')
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual);
        
        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->whereHas('servicio', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $servicios = $query->get();

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

    public function totalGanadoServicios($entidadId = null)
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae solo los servicios realizados del mes y año actual
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

        // Suma el total con descuento (lo que realmente se cobró)
        $totalServicios = $servicios->reduce(function ($carry, $item) {
            return $carry + ($item->total_con_descuento ?? ($item->cantidad * ($item->servicio->precio ?? 0)));
        }, 0);

        // Trae los ingresos adicionales del mes y año actual (excluyendo servicios ocasionales)
        $queryIngresos = IngresosAdicionales::whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->where('tipo', '!=', 'servicio_ocasional');
        
        // Filtrar ingresos adicionales por entidad si se proporciona
        if ($entidadId) {
            $queryIngresos->whereHas('operador', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $ingresosAdicionales = $queryIngresos->get();

        // Suma el total de ingresos adicionales (excluyendo servicios ocasionales)
        $totalIngresosAdicionales = $ingresosAdicionales->sum('monto');

        // Trae las ventas de productos del mes y año actual
        $queryVentas = \App\Http\Modules\Ventas\Models\Ventas::whereYear('created_at', $anioActual)
            ->whereMonth('created_at', $mesActual);
        
        // Filtrar ventas por entidad si se proporciona
        if ($entidadId) {
            $queryVentas->whereHas('empleado', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $ventas = $queryVentas->get();

        // Suma el total de ventas (se registra por el costo unitario)
        $totalVentas = $ventas->sum('total');

        // Total general (servicios + ingresos adicionales + ventas de productos)
        $total = $totalServicios + $totalIngresosAdicionales + $totalVentas;

        return $total;
    }

    public function calcularGananciaNeta($entidadId = null)
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae solo los servicios realizados del mes y año actual
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

        // Calcular ingresos totales de servicios (con descuento aplicado)
        $ingresosServicios = $servicios->reduce(function ($carry, $item) {
            return $carry + ($item->total_con_descuento ?? ($item->cantidad * ($item->servicio->precio ?? 0)));
        }, 0);

        // Trae los ingresos adicionales del mes y año actual (excluyendo servicios ocasionales)
        $queryIngresos = IngresosAdicionales::whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->where('tipo', '!=', 'servicio_ocasional');
        
        // Filtrar ingresos adicionales por entidad si se proporciona
        if ($entidadId) {
            $queryIngresos->whereHas('operador', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $ingresosAdicionales = $queryIngresos->get();

        // Suma el total de ingresos adicionales (excluyendo servicios ocasionales)
        $totalIngresosAdicionales = $ingresosAdicionales->sum('monto');

        // Trae las ventas de productos del mes y año actual
        $queryVentas = \App\Http\Modules\Ventas\Models\Ventas::whereYear('created_at', $anioActual)
            ->whereMonth('created_at', $mesActual);
        
        // Filtrar ventas por entidad si se proporciona
        if ($entidadId) {
            $queryVentas->whereHas('empleado', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $ventas = $queryVentas->get();

        // Suma el total de ventas (se registra por el costo unitario)
        $totalVentas = $ventas->sum('total');

        // Suma la ganancia total de las ventas (100% ganancia ya que no hay porcentaje de empleado)
        $gananciaVentas = $ventas->sum('ganancia_total');

        // Ingresos totales (servicios + ingresos adicionales + ventas de productos)
        $ingresosTotales = $ingresosServicios + $totalIngresosAdicionales + $totalVentas;

        // Calcular total a pagar a empleados (solo de servicios, no de ingresos adicionales)
        $totalPagarEmpleados = $servicios->reduce(function ($carry, $item) {
            $precio = $item->servicio->precio ?? 0;
            $porcentaje = $item->servicio->porcentaje_pago_empleado ?? 50;
            return $carry + ($item->cantidad * $precio * ($porcentaje / 100));
        }, 0);

        // Calcular ganancia neta (servicios - pagos a empleados) + ingresos adicionales + ganancia ventas
        // Los ingresos adicionales son 100% ganancia ya que no tienen porcentaje de empleado
        // Las ventas de productos son 100% ganancia ya que no tienen porcentaje de empleado
        $gananciaNeta = ($ingresosServicios - $totalPagarEmpleados) + $totalIngresosAdicionales + $gananciaVentas;

        // Calcular porcentaje de ganancia
        $porcentajeGanancia = $ingresosTotales > 0 ? ($gananciaNeta / $ingresosTotales) * 100 : 0;

        return [
            'ingresos_totales' => $ingresosTotales,
            'ingresos_servicios' => $ingresosServicios,
            'ingresos_adicionales' => $totalIngresosAdicionales,
            'ingresos_ventas' => $totalVentas,
            'ganancia_ventas' => $gananciaVentas,
            'total_pagar_empleados' => $totalPagarEmpleados,
            'ganancia_neta' => $gananciaNeta,
            'porcentaje_ganancia' => $porcentajeGanancia,
            'mes' => $mesActual,
            'anio' => $anioActual
        ];
    }

    public function gananciasPorMetodoPago($entidadId = null)
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae los servicios realizados del mes y año actual
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

        // Trae los ingresos adicionales del mes y año actual (excluyendo servicios ocasionales)
        $queryIngresos = IngresosAdicionales::whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->where('tipo', '!=', 'servicio_ocasional');
        
        // Filtrar ingresos adicionales por entidad si se proporciona
        if ($entidadId) {
            $queryIngresos->whereHas('operador', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $ingresosAdicionales = $queryIngresos->get();

        // Trae las ventas de productos del mes y año actual
        $queryVentas = \App\Http\Modules\Ventas\Models\Ventas::whereYear('created_at', $anioActual)
            ->whereMonth('created_at', $mesActual);
        
        // Filtrar ventas por entidad si se proporciona
        if ($entidadId) {
            $queryVentas->whereHas('empleado', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $ventas = $queryVentas->get();

        // Calcular totales por método de pago (servicios + ingresos adicionales + ventas)
        $totalEfectivo = $servicios->sum('monto_efectivo') + $ingresosAdicionales->sum('monto_efectivo') + $ventas->sum('monto_efectivo');
        $totalTransferencia = $servicios->sum('monto_transferencia') + $ingresosAdicionales->sum('monto_transferencia') + $ventas->sum('monto_transferencia');
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

        // Agregar ganancia de ventas de productos (100% ganancia)
        $gananciaEfectivo += $ventas->sum('monto_efectivo');
        $gananciaTransferencia += $ventas->sum('monto_transferencia');

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

    public function totalGananciasSeparadas($entidadId = null)
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae los servicios realizados del mes y año actual
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

        // Trae los ingresos adicionales del mes y año actual (excluyendo servicios ocasionales)
        $queryIngresos = IngresosAdicionales::whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->where('tipo', '!=', 'servicio_ocasional');
        
        // Filtrar ingresos adicionales por entidad si se proporciona
        if ($entidadId) {
            $queryIngresos->whereHas('operador', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $ingresosAdicionales = $queryIngresos->get();

        // Trae las ventas de productos del mes y año actual
        $ventas = \App\Http\Modules\Ventas\Models\Ventas::whereYear('created_at', $anioActual)
            ->whereMonth('created_at', $mesActual)
            ->get();

        // Calcular totales (servicios + ingresos adicionales + ventas)
        $totalEfectivo = $servicios->sum('monto_efectivo') + $ingresosAdicionales->sum('monto_efectivo') + $ventas->sum('monto_efectivo');
        $totalTransferencia = $servicios->sum('monto_transferencia') + $ingresosAdicionales->sum('monto_transferencia') + $ventas->sum('monto_transferencia');

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
        
        // Validar que los montos sumen el total
        $montoEfectivo = $data['monto_efectivo'] ?? 0;
        $montoTransferencia = $data['monto_transferencia'] ?? 0;
        $montoTotal = $data['monto'] ?? 0;
        
        // Validar que la suma de efectivo y transferencia sea igual al monto total
        $sumaMontos = $montoEfectivo + $montoTransferencia;
        if (abs($sumaMontos - $montoTotal) > 0.01) {
            throw new \Exception('La suma de efectivo y transferencia debe ser igual al monto total');
        }

        // La fecha ya viene en formato Y-m-d, no necesita procesamiento adicional

        // Si es un servicio ocasional y se proporciona operador_id, crear también el servicio realizado
        if (($data['tipo'] ?? '') === 'servicio_ocasional' && !empty($data['operador_id'])) {
            // Crear un servicio único para cada servicio ocasional
            $nombreServicioOcasional = 'Servicio Ocasional - ' . $data['concepto'] . ' - ' . date('Y-m-d H:i:s');
            $servicioOcasional = Servicios::create([
                'nombre' => $nombreServicioOcasional,
                'precio' => $montoTotal,
                'porcentaje_pago_empleado' => 40, // 40% para el operador
                'descripcion' => 'Servicio ocasional registrado desde ingresos adicionales: ' . $data['concepto'],
                'estado' => true
            ]);

            // Crear el servicio realizado
            $servicioRealizado = ServiciosRealizados::create([
                'servicio_id' => $servicioOcasional->id,
                'empleado_id' => $data['operador_id'],
                'cantidad' => 1,
                'fecha' => $data['fecha'],
                'metodo_pago' => $data['metodo_pago'] === 'mixto' ? 'efectivo' : $data['metodo_pago'],
                'monto_efectivo' => $montoEfectivo,
                'monto_transferencia' => $montoTransferencia,
                'total_servicio' => $montoTotal,
                'descuento_porcentaje' => 0,
                'monto_descuento' => 0,
                'total_con_descuento' => $montoTotal
            ]);

            // Agregar el ID del servicio realizado al ingreso adicional
            $data['servicio_realizado_id'] = $servicioRealizado->id;
        }
        
        return IngresosAdicionales::create($data);
    }

    public function listarIngresosAdicionales($entidadId = null)
    {
        $query = IngresosAdicionales::with([
            'empleado:id,nombre,apellido',
            'operador:id,nombre,apellido',
            'servicioRealizado.servicio:id,nombre,precio'
        ])
            ->orderBy('created_at', 'desc');
        
        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->whereHas('operador', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        return $query->get()
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
                    'operador_id' => $item->operador_id,
                    'servicio_realizado_id' => $item->servicio_realizado_id,
                    'fecha' => $item->fecha,
                    'empleado' => $item->empleado ? [
                        'id' => $item->empleado->id,
                        'nombre' => $item->empleado->nombre,
                        'apellido' => $item->empleado->apellido,
                    ] : null,
                    'operador' => $item->operador ? [
                        'id' => $item->operador->id,
                        'nombre' => $item->operador->nombre,
                        'apellido' => $item->operador->apellido,
                    ] : null,
                    'servicio_realizado' => $item->servicioRealizado ? [
                        'id' => $item->servicioRealizado->id,
                        'servicio' => $item->servicioRealizado->servicio ? [
                            'id' => $item->servicioRealizado->servicio->id,
                            'nombre' => $item->servicioRealizado->servicio->nombre,
                            'precio' => $item->servicioRealizado->servicio->precio,
                        ] : null,
                    ] : null,
                ];
            });
    }

    public function totalIngresosAdicionales($entidadId = null)
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae los ingresos adicionales del mes y año actual
        $query = IngresosAdicionales::whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual);
        
        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->whereHas('operador', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $ingresos = $query->get();

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

    public function estadisticasCompletas($entidadId = null)
    {
        // Obtiene el mes y año actual
        $mesActual = date('m');
        $anioActual = date('Y');

        // Trae los servicios realizados del mes y año actual
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

        // Trae los ingresos adicionales del mes y año actual (excluyendo servicios ocasionales)
        $queryIngresos = IngresosAdicionales::whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->where('tipo', '!=', 'servicio_ocasional');
        
        // Filtrar ingresos adicionales por entidad si se proporciona
        if ($entidadId) {
            $queryIngresos->whereHas('operador', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $ingresosAdicionales = $queryIngresos->get();

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

    public function gananciasDiarias($fecha, $entidadId = null)
    {
        // Trae los servicios realizados de la fecha específica
        $query = ServiciosRealizados::with('servicio', 'empleado')
            ->whereDate('fecha', $fecha);
        
        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->whereHas('servicio', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $servicios = $query->get();

        // Trae los ingresos adicionales de la fecha específica
        $queryIngresos = IngresosAdicionales::whereDate('fecha', $fecha);
        
        // Filtrar ingresos adicionales por entidad si se proporciona
        if ($entidadId) {
            $queryIngresos->whereHas('operador', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $ingresosAdicionales = $queryIngresos->get();

        // Trae las ventas de productos de la fecha específica
        $queryVentas = \App\Http\Modules\Ventas\Models\Ventas::whereDate('created_at', $fecha);
        
        // Filtrar ventas por entidad si se proporciona
        if ($entidadId) {
            $queryVentas->whereHas('empleado', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $ventas = $queryVentas->get();

        // Calcular ingresos de servicios
        $ingresosServicios = $servicios->reduce(function ($carry, $item) {
            return $carry + ($item->total_con_descuento ?? ($item->cantidad * ($item->servicio->precio ?? 0)));
        }, 0);

        // Filtrar ingresos adicionales para evitar doble conteo con servicios ocasionales
        // Si un servicio ocasional ya está en servicios_realizados, no lo contamos en ingresos_adicionales
        $ingresosAdicionalesFiltrados = $ingresosAdicionales->filter(function ($ingreso) use ($servicios) {
            // Si es un servicio ocasional, verificar si ya está en servicios_realizados
            if ($ingreso->tipo === 'servicio_ocasional' && $ingreso->servicio_realizado_id) {
                // Si tiene servicio_realizado_id, ya está contado en servicios_realizados
                return false;
            }
            return true;
        });

        // Calcular ingresos adicionales (excluyendo servicios ocasionales ya contados en servicios)
        $ingresosAdicionalesTotal = $ingresosAdicionalesFiltrados->sum('monto');

        // Para el desglose de cantidad, contar TODOS los servicios ocasionales (incluyendo los que ya están en servicios_realizados)
        $serviciosOcasionalesCount = $ingresosAdicionales->where('tipo', 'servicio_ocasional')->count();

        // Calcular ingresos de ventas de productos
        $ingresosVentas = $ventas->sum('total');

        // Ingresos totales del día
        $ingresosTotales = $ingresosServicios + $ingresosAdicionalesTotal + $ingresosVentas;

        // Calcular pagos a empleados (solo de servicios)
        $totalPagarEmpleados = $servicios->reduce(function ($carry, $item) {
            $precio = $item->servicio->precio ?? 0;
            $porcentaje = $item->servicio->porcentaje_pago_empleado ?? 50;
            return $carry + ($item->cantidad * $precio * ($porcentaje / 100));
        }, 0);

        // Calcular ganancia de ventas de productos (100% ganancia ya que no hay porcentaje de empleado)
        $gananciaVentas = $ventas->sum('ganancia_total');

        // Ganancia neta del día (servicios + ingresos adicionales + ventas de productos)
        // Servicios: ingresos - pagos a empleados
        // Ingresos adicionales: 100% ganancia (no hay porcentaje de empleado)
        // Ventas de productos: 100% ganancia (no hay porcentaje de empleado)
        $gananciaNeta = ($ingresosServicios - $totalPagarEmpleados) + $ingresosAdicionalesTotal + $gananciaVentas;

        // Métodos de pago
        $efectivoServicios = $servicios->sum('monto_efectivo');
        $transferenciaServicios = $servicios->sum('monto_transferencia');
        $efectivoAdicionales = $ingresosAdicionalesFiltrados->sum('monto_efectivo');
        $transferenciaAdicionales = $ingresosAdicionalesFiltrados->sum('monto_transferencia');
        $efectivoVentas = $ventas->sum('monto_efectivo');
        $transferenciaVentas = $ventas->sum('monto_transferencia');

        // Totales por método de pago
        $totalEfectivo = $efectivoServicios + $efectivoAdicionales + $efectivoVentas;
        $totalTransferencia = $transferenciaServicios + $transferenciaAdicionales + $transferenciaVentas;

        // Desglose por tipo de ingreso adicional
        $accesorios = $ingresosAdicionalesFiltrados->where('tipo', 'accesorio')->sum('monto');
        $serviciosOcasionales = $serviciosOcasionalesCount; // Usar la cantidad total de servicios ocasionales
        $otros = $ingresosAdicionalesFiltrados->where('tipo', 'otro')->sum('monto');

        // Detalles de servicios por empleado
        $serviciosPorEmpleado = $servicios->groupBy('empleado_id')->map(function ($items, $empleadoId) {
            $empleado = $items->first()->empleado;
            
            // Calcular total bruto (precio original sin descuento)
            $totalBruto = $items->reduce(function ($carry, $item) {
                return $carry + ($item->cantidad * ($item->servicio->precio ?? 0));
            }, 0);
            
            // Calcular total a pagar al empleado
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
                'total_bruto' => $totalBruto,
                'total_pagar' => $totalEmpleado,
                'servicios' => $items->map(function ($item) {
                    return [
                        'servicio_nombre' => $item->servicio->nombre ?? 'N/A',
                        'cantidad' => $item->cantidad,
                        'precio_unitario' => $item->servicio->precio ?? 0,
                        'porcentaje_empleado' => $item->servicio->porcentaje_pago_empleado ?? 50,
                        'total_bruto_servicio' => $item->cantidad * ($item->servicio->precio ?? 0),
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
                'ingresos_ventas' => $ingresosVentas,
                'total_pagar_empleados' => $totalPagarEmpleados,
                'ganancia_neta' => $gananciaNeta,
                'porcentaje_ganancia' => $ingresosTotales > 0 ? ($gananciaNeta / $ingresosTotales) * 100 : 0
            ],
            'metodos_pago' => [
                'efectivo' => [
                    'total' => $totalEfectivo,
                    'servicios' => $efectivoServicios,
                    'adicionales' => $efectivoAdicionales,
                    'ventas' => $efectivoVentas
                ],
                'transferencia' => [
                    'total' => $totalTransferencia,
                    'servicios' => $transferenciaServicios,
                    'adicionales' => $transferenciaAdicionales,
                    'ventas' => $transferenciaVentas
                ]
            ],
            'ingresos_adicionales_detalle' => [
                'accesorios' => $accesorios,
                'servicios_ocasionales' => $serviciosOcasionales,
                'otros' => $otros,
                'total_registros' => $ingresosAdicionalesFiltrados->count()
            ],
            'ventas_productos' => [
                'total_ventas' => $ingresosVentas,
                'ganancia_ventas' => $gananciaVentas,
                'cantidad_ventas' => $ventas->count(),
                'efectivo' => $efectivoVentas,
                'transferencia' => $transferenciaVentas
            ],
            'servicios_por_empleado' => $serviciosPorEmpleado,
            'estadisticas' => [
                'total_servicios' => $servicios->count(),
                'cantidad_empleados' => $servicios->unique('empleado_id')->count(),
                'promedio_por_servicio' => $servicios->count() > 0 ? $ingresosServicios / $servicios->count() : 0,
                'total_ventas_productos' => $ventas->count(),
                'promedio_por_venta' => $ventas->count() > 0 ? $ingresosVentas / $ventas->count() : 0
            ]
        ];
    }

    public function gananciasPorRango($fechaInicio, $fechaFin, $entidadId = null)
    {
        // Trae los servicios realizados en el rango de fechas
        $queryServicios = ServiciosRealizados::with('servicio')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        
        // Filtrar servicios por entidad si se proporciona
        if ($entidadId) {
            $queryServicios->whereHas('servicio', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $servicios = $queryServicios->get();

        // Trae los ingresos adicionales en el rango de fechas (excluyendo servicios ocasionales)
        $queryIngresos = IngresosAdicionales::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('tipo', '!=', 'servicio_ocasional');
        
        // Filtrar ingresos adicionales por entidad si se proporciona
        if ($entidadId) {
            $queryIngresos->whereHas('operador', function ($q) use ($entidadId) {
                $q->where('entidad_id', $entidadId);
            });
        }
        
        $ingresosAdicionales = $queryIngresos->get();

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

    public function eliminarIngresoAdicional(int $id)
    {
        $ingreso = IngresosAdicionales::find($id);
        
        if (!$ingreso) {
            throw new \Exception('El ingreso adicional no existe');
        }
        
        // Si es un servicio ocasional, eliminar también el servicio realizado y el servicio
        if ($ingreso->tipo === 'servicio_ocasional' && $ingreso->servicio_realizado_id) {
            $servicioRealizado = ServiciosRealizados::find($ingreso->servicio_realizado_id);
            if ($servicioRealizado) {
                // Eliminar el servicio realizado
                $servicioRealizado->delete();
                
                // Eliminar el servicio ocasional
                if ($servicioRealizado->servicio) {
                    $servicioRealizado->servicio->delete();
                }
            }
        }
        
        $ingreso->delete();
        
        return [
            'message' => 'Ingreso adicional eliminado exitosamente',
            'id' => $id
        ];
    }

    public function serviciosMultiples(array $data)
    {
        // Validar que los montos sumen el total de todos los servicios
        $montoEfectivo = $data['monto_efectivo'] ?? 0;
        $montoTransferencia = $data['monto_transferencia'] ?? 0;
        $servicios = $data['servicios'] ?? [];
        
        // Calcular el total de todos los servicios
        $totalServicios = 0;
        $serviciosConCalculos = [];
        
        foreach ($servicios as $servicio) {
            $servicioModel = Servicios::find($servicio['servicio_id']);
            if (!$servicioModel) {
                throw new \Exception("El servicio con ID {$servicio['servicio_id']} no existe");
            }
            
            $precio = $servicioModel->precio ?? 0;
            $cantidad = $servicio['cantidad'] ?? 0;
            $descuentoPorcentaje = $servicio['descuento_porcentaje'] ?? 0;
            
            $totalServicio = $precio * $cantidad;
            $montoDescuento = $totalServicio * ($descuentoPorcentaje / 100);
            $totalConDescuento = $totalServicio - $montoDescuento;
            
            $serviciosConCalculos[] = [
                'servicio_id' => $servicio['servicio_id'],
                'cantidad' => $cantidad,
                'descuento_porcentaje' => $descuentoPorcentaje,
                'total_servicio' => $totalServicio,
                'monto_descuento' => $montoDescuento,
                'total_con_descuento' => $totalConDescuento
            ];
            
            $totalServicios += $totalConDescuento;
        }
        
        // Validar que la suma de efectivo y transferencia sea igual al total de todos los servicios
        $sumaMontos = $montoEfectivo + $montoTransferencia;
        if (abs($sumaMontos - $totalServicios) > 0.01) {
            throw new \Exception('La suma de efectivo y transferencia debe ser igual al total de todos los servicios con descuentos aplicados');
        }
        
        // Usar transacción para asegurar que todos los servicios se creen o ninguno
        return DB::transaction(function () use ($data, $serviciosConCalculos, $montoEfectivo, $montoTransferencia, $totalServicios) {
            $serviciosCreados = [];
            $montoEfectivoRestante = $montoEfectivo;
            $montoTransferenciaRestante = $montoTransferencia;
            
            foreach ($serviciosConCalculos as $index => $servicioCalculado) {
                // Calcular la proporción de este servicio respecto al total
                $proporcion = $totalServicios > 0 ? $servicioCalculado['total_con_descuento'] / $totalServicios : 0;
                
                // Distribuir los montos proporcionalmente
                $montoEfectivoServicio = round($montoEfectivo * $proporcion, 2);
                $montoTransferenciaServicio = round($montoTransferencia * $proporcion, 2);
                
                // Para el último servicio, usar los montos restantes para evitar errores de redondeo
                if ($index === count($serviciosConCalculos) - 1) {
                    $montoEfectivoServicio = $montoEfectivoRestante;
                    $montoTransferenciaServicio = $montoTransferenciaRestante;
                } else {
                    // Actualizar montos restantes
                    $montoEfectivoRestante -= $montoEfectivoServicio;
                    $montoTransferenciaRestante -= $montoTransferenciaServicio;
                }
                
                $servicioData = [
                    'empleado_id' => $data['empleado_id'],
                    'servicio_id' => $servicioCalculado['servicio_id'],
                    'cantidad' => $servicioCalculado['cantidad'],
                    'fecha' => $data['fecha'],
                    'metodo_pago' => $data['metodo_pago'],
                    'monto_efectivo' => $montoEfectivoServicio,
                    'monto_transferencia' => $montoTransferenciaServicio,
                    'total_servicio' => $servicioCalculado['total_servicio'],
                    'descuento_porcentaje' => $servicioCalculado['descuento_porcentaje'],
                    'monto_descuento' => $servicioCalculado['monto_descuento'],
                    'total_con_descuento' => $servicioCalculado['total_con_descuento']
                ];
                
                // Si la fecha viene solo como Y-m-d, agregar la hora actual
                if (isset($servicioData['fecha']) && !str_contains($servicioData['fecha'], ':')) {
                    // Usar Carbon con zona horaria explícita para asegurar la hora correcta
                    $horaActual = \Carbon\Carbon::now('America/Bogota')->format('H:i:s');
                    $servicioData['fecha'] = $servicioData['fecha'] . ' ' . $horaActual;
                    
                    // Log para debug
                    \Log::info('Servicios múltiples - Fecha original: ' . $servicioData['fecha'] . ' - Hora actual: ' . $horaActual);
                }
                
                $serviciosCreados[] = ServiciosRealizados::create($servicioData);
            }
            
            return [
                'message' => 'Servicios creados exitosamente',
                'servicios_creados' => count($serviciosCreados),
                'total_general' => $data['monto_efectivo'] + $data['monto_transferencia']
            ];
        });
    }
}
