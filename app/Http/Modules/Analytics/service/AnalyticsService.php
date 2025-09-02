<?php

namespace App\Http\Modules\Analytics\service;

use App\Http\Modules\servicios\models\ServiciosRealizados;
use App\Http\Modules\servicios\models\Servicios;
use App\Http\Modules\servicios\models\IngresosAdicionales;
use App\Http\Modules\Operadores\Models\Operadores;
use App\Http\Modules\Pagos\Models\Pagos;
use App\Http\Modules\Entidades\models\Entidades;
use App\Http\Modules\Gastos\models\GastosOperativos;
use App\Http\Modules\Ventas\Models\Ventas;
use App\Http\Modules\Productos\Models\Productos;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class AnalyticsService
{
    public function getAvailableReportTypes(): array
    {
        return [
            [
                'id' => 'servicios_mas_utilizados',
                'name' => 'Servicios Más Utilizados',
                'description' => 'Análisis de los servicios más realizados por período',
                'category' => 'servicios'
            ],
            [
                'id' => 'operadores_mas_activos',
                'name' => 'Operadores Más Activos',
                'description' => 'Ranking de operadores por cantidad de servicios realizados',
                'category' => 'operadores'
            ],
            [
                'id' => 'rendimiento_operadores',
                'name' => 'Rendimiento de Operadores',
                'description' => 'Análisis de rendimiento y ganancias por operador',
                'category' => 'operadores'
            ],
            [
                'id' => 'ingresos_por_servicio',
                'name' => 'Ingresos por Servicio',
                'description' => 'Análisis de ingresos generados por cada tipo de servicio',
                'category' => 'financiero'
            ],
            [
                'id' => 'metodos_pago_analisis',
                'name' => 'Análisis de Métodos de Pago',
                'description' => 'Distribución de pagos por método de pago',
                'category' => 'financiero'
            ],
            [
                'id' => 'tendencias_temporales',
                'name' => 'Tendencias Temporales',
                'description' => 'Análisis de tendencias de servicios por día/semana/mes',
                'category' => 'tendencias'
            ],
            [
                'id' => 'entidades_rendimiento',
                'name' => 'Rendimiento por Entidad',
                'description' => 'Análisis de servicios y ganancias por entidad',
                'category' => 'entidades'
            ],
            [
                'id' => 'reporte_completo_ganancias',
                'name' => 'Reporte Completo de Ganancias',
                'description' => 'Análisis completo de pagos, gastos, ingresos y ganancia neta por operador',
                'category' => 'financiero'
            ]
        ];
    }

    public function generateReport(string $reportType, ?string $startDate = null, ?string $endDate = null, array $filters = []): array
    {
        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : Carbon::now()->endOfMonth();

        // Si no hay filtros de entidad, obtener la entidad del usuario autenticado
        if (empty($filters['entidad_id'])) {
            $user = auth()->user();
            if ($user && $user->operador && !$user->esAdmin()) {
                $filters['entidad_id'] = $user->operador->entidad_id;
            }
        }

        return match ($reportType) {
            'servicios_mas_utilizados' => $this->getServiciosMasUtilizados($startDate, $endDate, $filters),
            'operadores_mas_activos' => $this->getOperadoresMasActivos($startDate, $endDate, $filters),
            'rendimiento_operadores' => $this->getRendimientoOperadores($startDate, $endDate, $filters),
            'ingresos_por_servicio' => $this->getIngresosPorServicio($startDate, $endDate, $filters),
            'metodos_pago_analisis' => $this->getMetodosPagoAnalisis($startDate, $endDate, $filters),
            'tendencias_temporales' => $this->getTendenciasTemporales($startDate, $endDate, $filters),
            'entidades_rendimiento' => $this->getEntidadesRendimiento($startDate, $endDate, $filters),
            'reporte_completo_ganancias' => $this->getReporteCompletoGanancias($startDate, $endDate, $filters),
            default => throw new \InvalidArgumentException('Tipo de reporte no válido')
        };
    }

    private function getServiciosMasUtilizados(Carbon $startDate, Carbon $endDate, array $filters): array
    {
        $query = ServiciosRealizados::select([
                'servicios.nombre as servicio',
                DB::raw('COUNT(*) as cantidad_realizada'),
                DB::raw('SUM(servicios_realizados.total_servicio) as ingresos_totales'),
                DB::raw('AVG(servicios_realizados.total_servicio) as promedio_por_servicio')
            ])
            ->join('servicios', 'servicios_realizados.servicio_id', '=', 'servicios.id')
            ->whereBetween('servicios_realizados.fecha', [$startDate, $endDate])
            ->groupBy('servicios.id', 'servicios.nombre')
            ->orderBy('cantidad_realizada', 'desc');

        if (!empty($filters['entidad_id'])) {
            $query->join('operadores', 'servicios_realizados.empleado_id', '=', 'operadores.id')
                  ->where('operadores.entidad_id', $filters['entidad_id']);
        }

        $data = $query->get();

        return [
            'title' => 'Servicios Más Utilizados',
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'columns' => [
                ['key' => 'servicio', 'header' => 'Servicio'],
                ['key' => 'cantidad_realizada', 'header' => 'Cantidad Realizada'],
                ['key' => 'ingresos_totales', 'header' => 'Ingresos Totales ($)'],
                ['key' => 'promedio_por_servicio', 'header' => 'Promedio por Servicio ($)']
            ],
            'data' => $data,
            'summary' => [
                'total_servicios' => $data->sum('cantidad_realizada'),
                'total_ingresos' => $data->sum('ingresos_totales'),
                'servicio_mas_popular' => $data->first()?->servicio ?? 'N/A'
            ]
        ];
    }

    private function getOperadoresMasActivos(Carbon $startDate, Carbon $endDate, array $filters): array
    {
        $query = ServiciosRealizados::select([
                DB::raw('CONCAT(operadores.nombre, " ", operadores.apellido) as operador'),
                'entidades.nombre as entidad',
                'cargos.nombre as cargo',
                DB::raw('COUNT(*) as servicios_realizados'),
                DB::raw('SUM(servicios_realizados.total_servicio) as ingresos_generados'),
                DB::raw('AVG(servicios_realizados.total_servicio) as promedio_por_servicio')
            ])
            ->join('operadores', 'servicios_realizados.empleado_id', '=', 'operadores.id')
            ->join('entidades', 'operadores.entidad_id', '=', 'entidades.id')
            ->join('cargos', 'operadores.cargo_id', '=', 'cargos.id')
            ->whereBetween('servicios_realizados.fecha', [$startDate, $endDate])
            ->groupBy('operadores.id', 'operadores.nombre', 'operadores.apellido', 'entidades.nombre', 'cargos.nombre')
            ->orderBy('servicios_realizados', 'desc');

        if (!empty($filters['entidad_id'])) {
            $query->where('operadores.entidad_id', $filters['entidad_id']);
        }

        $data = $query->get();

        return [
            'title' => 'Operadores Más Activos',
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'columns' => [
                ['key' => 'operador', 'header' => 'Operador'],
                ['key' => 'entidad', 'header' => 'Entidad'],
                ['key' => 'cargo', 'header' => 'Cargo'],
                ['key' => 'servicios_realizados', 'header' => 'Servicios Realizados'],
                ['key' => 'ingresos_generados', 'header' => 'Ingresos Generados ($)'],
                ['key' => 'promedio_por_servicio', 'header' => 'Promedio por Servicio ($)']
            ],
            'data' => $data,
            'summary' => [
                'total_operadores' => $data->count(),
                'total_servicios' => $data->sum('servicios_realizados'),
                'total_ingresos' => $data->sum('ingresos_generados'),
                'operador_mas_activo' => $data->first()?->operador ?? 'N/A'
            ]
        ];
    }

    private function getRendimientoOperadores(Carbon $startDate, Carbon $endDate, array $filters): array
    {
        $query = ServiciosRealizados::select([
                DB::raw('CONCAT(operadores.nombre, " ", operadores.apellido) as operador'),
                'entidades.nombre as entidad',
                DB::raw('COUNT(*) as servicios_realizados'),
                DB::raw('SUM(servicios_realizados.total_servicio) as ingresos_totales'),
                DB::raw('SUM(servicios_realizados.monto_efectivo) as pagos_efectivo'),
                DB::raw('SUM(servicios_realizados.monto_transferencia) as pagos_transferencia'),
                DB::raw('AVG(servicios_realizados.total_servicio) as promedio_por_servicio'),
                DB::raw('COUNT(DISTINCT DATE(servicios_realizados.fecha)) as dias_trabajados')
            ])
            ->join('operadores', 'servicios_realizados.empleado_id', '=', 'operadores.id')
            ->join('entidades', 'operadores.entidad_id', '=', 'entidades.id')
            ->whereBetween('servicios_realizados.fecha', [$startDate, $endDate])
            ->groupBy('operadores.id', 'operadores.nombre', 'operadores.apellido', 'entidades.nombre')
            ->orderBy('ingresos_totales', 'desc');

        if (!empty($filters['entidad_id'])) {
            $query->where('operadores.entidad_id', $filters['entidad_id']);
        }

        $data = $query->get();

        return [
            'title' => 'Rendimiento de Operadores',
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'columns' => [
                ['key' => 'operador', 'header' => 'Operador'],
                ['key' => 'entidad', 'header' => 'Entidad'],
                ['key' => 'servicios_realizados', 'header' => 'Servicios Realizados'],
                ['key' => 'ingresos_totales', 'header' => 'Ingresos Totales ($)'],
                ['key' => 'pagos_efectivo', 'header' => 'Pagos Efectivo ($)'],
                ['key' => 'pagos_transferencia', 'header' => 'Pagos Transferencia ($)'],
                ['key' => 'promedio_por_servicio', 'header' => 'Promedio por Servicio ($)'],
                ['key' => 'dias_trabajados', 'header' => 'Días Trabajados']
            ],
            'data' => $data,
            'summary' => [
                'total_operadores' => $data->count(),
                'total_servicios' => $data->sum('servicios_realizados'),
                'total_ingresos' => $data->sum('ingresos_totales'),
                'promedio_servicios_por_operador' => round($data->avg('servicios_realizados'), 2)
            ]
        ];
    }

    private function getIngresosPorServicio(Carbon $startDate, Carbon $endDate, array $filters): array
    {
        $query = ServiciosRealizados::select([
                'servicios.nombre as servicio',
                DB::raw('COUNT(*) as cantidad_realizada'),
                DB::raw('SUM(servicios_realizados.total_servicio) as ingresos_totales'),
                DB::raw('SUM(servicios_realizados.monto_efectivo) as ingresos_efectivo'),
                DB::raw('SUM(servicios_realizados.monto_transferencia) as ingresos_transferencia'),
                DB::raw('AVG(servicios_realizados.total_servicio) as promedio_por_servicio')
            ])
            ->join('servicios', 'servicios_realizados.servicio_id', '=', 'servicios.id')
            ->whereBetween('servicios_realizados.fecha', [$startDate, $endDate])
            ->groupBy('servicios.id', 'servicios.nombre')
            ->orderBy('ingresos_totales', 'desc');

        if (!empty($filters['entidad_id'])) {
            $query->join('operadores', 'servicios_realizados.empleado_id', '=', 'operadores.id')
                  ->where('operadores.entidad_id', $filters['entidad_id']);
        }

        $data = $query->get();

        return [
            'title' => 'Ingresos por Servicio',
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'columns' => [
                ['key' => 'servicio', 'header' => 'Servicio'],
                ['key' => 'cantidad_realizada', 'header' => 'Cantidad Realizada'],
                ['key' => 'ingresos_totales', 'header' => 'Ingresos Totales ($)'],
                ['key' => 'ingresos_efectivo', 'header' => 'Ingresos Efectivo ($)'],
                ['key' => 'ingresos_transferencia', 'header' => 'Ingresos Transferencia ($)'],
                ['key' => 'promedio_por_servicio', 'header' => 'Promedio por Servicio ($)']
            ],
            'data' => $data,
            'summary' => [
                'total_ingresos' => $data->sum('ingresos_totales'),
                'total_efectivo' => $data->sum('ingresos_efectivo'),
                'total_transferencia' => $data->sum('ingresos_transferencia'),
                'servicio_mas_rentable' => $data->first()?->servicio ?? 'N/A'
            ]
        ];
    }

    private function getMetodosPagoAnalisis(Carbon $startDate, Carbon $endDate, array $filters): array
    {
        $query = ServiciosRealizados::select([
                DB::raw('CASE 
                    WHEN monto_efectivo > 0 AND monto_transferencia > 0 THEN "Mixto"
                    WHEN monto_efectivo > 0 THEN "Efectivo"
                    WHEN monto_transferencia > 0 THEN "Transferencia"
                    ELSE "No especificado"
                END as metodo_pago'),
                DB::raw('COUNT(*) as cantidad_transacciones'),
                DB::raw('SUM(total_servicio) as monto_total'),
                DB::raw('AVG(total_servicio) as promedio_por_transaccion')
            ])
            ->whereBetween('fecha', [$startDate, $endDate])
            ->groupBy('metodo_pago')
            ->orderBy('monto_total', 'desc');

        if (!empty($filters['entidad_id'])) {
            $query->join('operadores', 'servicios_realizados.empleado_id', '=', 'operadores.id')
                  ->where('operadores.entidad_id', $filters['entidad_id']);
        }

        $data = $query->get();

        return [
            'title' => 'Análisis de Métodos de Pago',
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'columns' => [
                ['key' => 'metodo_pago', 'header' => 'Método de Pago'],
                ['key' => 'cantidad_transacciones', 'header' => 'Cantidad de Transacciones'],
                ['key' => 'monto_total', 'header' => 'Monto Total ($)'],
                ['key' => 'promedio_por_transaccion', 'header' => 'Promedio por Transacción ($)']
            ],
            'data' => $data,
            'summary' => [
                'total_transacciones' => $data->sum('cantidad_transacciones'),
                'total_monto' => $data->sum('monto_total'),
                'metodo_mas_popular' => $data->first()?->metodo_pago ?? 'N/A'
            ]
        ];
    }

    private function getTendenciasTemporales(Carbon $startDate, Carbon $endDate, array $filters): array
    {
        $query = ServiciosRealizados::select([
                DB::raw('DATE(fecha) as fecha'),
                DB::raw('COUNT(*) as servicios_realizados'),
                DB::raw('SUM(total_servicio) as ingresos_diarios'),
                DB::raw('AVG(total_servicio) as promedio_por_servicio')
            ])
            ->whereBetween('fecha', [$startDate, $endDate])
            ->groupBy('fecha')
            ->orderBy('fecha');

        if (!empty($filters['entidad_id'])) {
            $query->join('operadores', 'servicios_realizados.empleado_id', '=', 'operadores.id')
                  ->where('operadores.entidad_id', $filters['entidad_id']);
        }

        $data = $query->get();

        return [
            'title' => 'Tendencias Temporales',
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'columns' => [
                ['key' => 'fecha', 'header' => 'Fecha'],
                ['key' => 'servicios_realizados', 'header' => 'Servicios Realizados'],
                ['key' => 'ingresos_diarios', 'header' => 'Ingresos Diarios ($)'],
                ['key' => 'promedio_por_servicio', 'header' => 'Promedio por Servicio ($)']
            ],
            'data' => $data,
            'summary' => [
                'total_dias' => $data->count(),
                'total_servicios' => $data->sum('servicios_realizados'),
                'total_ingresos' => $data->sum('ingresos_diarios'),
                'promedio_servicios_por_dia' => round($data->avg('servicios_realizados'), 2)
            ]
        ];
    }

    private function getEntidadesRendimiento(Carbon $startDate, Carbon $endDate, array $filters): array
    {
        $query = ServiciosRealizados::select([
                'entidades.nombre as entidad',
                DB::raw('COUNT(*) as servicios_realizados'),
                DB::raw('SUM(servicios_realizados.total_servicio) as ingresos_totales'),
                DB::raw('COUNT(DISTINCT servicios_realizados.empleado_id) as operadores_activos'),
                DB::raw('AVG(servicios_realizados.total_servicio) as promedio_por_servicio')
            ])
            ->join('operadores', 'servicios_realizados.empleado_id', '=', 'operadores.id')
            ->join('entidades', 'operadores.entidad_id', '=', 'entidades.id')
            ->whereBetween('servicios_realizados.fecha', [$startDate, $endDate])
            ->groupBy('entidades.id', 'entidades.nombre')
            ->orderBy('ingresos_totales', 'desc');

        $data = $query->get();

        return [
            'title' => 'Rendimiento por Entidad',
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'columns' => [
                ['key' => 'entidad', 'header' => 'Entidad'],
                ['key' => 'servicios_realizados', 'header' => 'Servicios Realizados'],
                ['key' => 'ingresos_totales', 'header' => 'Ingresos Totales ($)'],
                ['key' => 'operadores_activos', 'header' => 'Operadores Activos'],
                ['key' => 'promedio_por_servicio', 'header' => 'Promedio por Servicio ($)']
            ],
            'data' => $data,
            'summary' => [
                'total_entidades' => $data->count(),
                'total_servicios' => $data->sum('servicios_realizados'),
                'total_ingresos' => $data->sum('ingresos_totales'),
                'entidad_mas_rentable' => $data->first()?->entidad ?? 'N/A'
            ]
        ];
    }

    private function getReporteCompletoGanancias(Carbon $startDate, Carbon $endDate, array $filters): array
    {
        // Obtener todos los operadores con sus servicios realizados del período específico
        $operadoresQuery = Operadores::with(['serviciosRealizados' => function($query) use ($startDate, $endDate) {
            $query->whereDate('fecha', '>=', $startDate->toDateString())
                  ->whereDate('fecha', '<=', $endDate->toDateString())
                  ->with('servicio:id,nombre,precio,porcentaje_pago_empleado');
        }]);

        // Aplicar filtro por entidad si se proporciona
        if (!empty($filters['entidad_id'])) {
            $operadoresQuery->where('entidad_id', $filters['entidad_id']);
        }

        $operadores = $operadoresQuery->get();

        $data = [];
        $totalIngresosServicios = 0;
        $totalIngresosProductos = 0;
        $totalIngresosAdicionales = 0;
        $totalGastos = 0;
        $totalPagosOperadores = 0;

        foreach ($operadores as $operador) {
            // Calcular ingresos por servicios del operador en el período específico
            $ingresosServicios = $operador->serviciosRealizados->sum(function($servicio) {
                return $servicio->total_con_descuento ?? ($servicio->cantidad * ($servicio->servicio->precio ?? 0));
            });

            // Calcular pagos al operador por servicios en el período específico
            $pagosServicios = $operador->serviciosRealizados->sum(function($servicio) {
                $precio = $servicio->servicio->precio ?? 0;
                $porcentaje = $servicio->servicio->porcentaje_pago_empleado ?? 50;
                return $servicio->cantidad * $precio * ($porcentaje / 100);
            });

            // Calcular pagos realizados al operador en el período específico
            $pagosRealizados = Pagos::where('empleado_id', $operador->id)
                ->whereDate('created_at', '>=', $startDate->toDateString())
                ->whereDate('created_at', '<=', $endDate->toDateString())
                ->sum('monto');

            // Calcular ganancia neta del operador (ingresos - pagos realizados)
            $gananciaNetaOperador = $ingresosServicios - $pagosRealizados;

            $data[] = [
                'operador' => $operador->nombre . ' ' . $operador->apellido,
                'entidad' => $operador->entidad->nombre ?? 'N/A',
                'ingresos_servicios' => $ingresosServicios,
                'pagos_servicios' => $pagosServicios,
                'pagos_realizados' => $pagosRealizados,
                'pagos_pendientes' => max(0, $pagosServicios - $pagosRealizados),
                'ganancia_neta_operador' => $gananciaNetaOperador,
                'cantidad_servicios' => $operador->serviciosRealizados->count(),
                'cantidad_pagos' => Pagos::where('empleado_id', $operador->id)
                    ->whereDate('created_at', '>=', $startDate->toDateString())
                    ->whereDate('created_at', '<=', $endDate->toDateString())
                    ->count()
            ];

            $totalIngresosServicios += $ingresosServicios;
            $totalPagosOperadores += $pagosRealizados;
        }

        // Calcular ingresos por ventas de productos en el período específico
        $ventasQuery = Ventas::whereDate('created_at', '>=', $startDate->toDateString())
            ->whereDate('created_at', '<=', $endDate->toDateString());
        if (!empty($filters['entidad_id'])) {
            $ventasQuery->whereHas('empleado', function($q) use ($filters) {
                $q->where('entidad_id', $filters['entidad_id']);
            });
        }
        $totalIngresosProductos = $ventasQuery->sum('total');

        // Calcular ingresos adicionales en el período específico
        $ingresosAdicionalesQuery = IngresosAdicionales::whereDate('fecha', '>=', $startDate->toDateString())
            ->whereDate('fecha', '<=', $endDate->toDateString())
            ->where('tipo', '!=', 'servicio_ocasional');
        if (!empty($filters['entidad_id'])) {
            $ingresosAdicionalesQuery->whereHas('empleado', function($q) use ($filters) {
                $q->where('entidad_id', $filters['entidad_id']);
            });
        }
        $totalIngresosAdicionales = $ingresosAdicionalesQuery->sum('monto');

        // Calcular gastos operativos en el período específico
        $gastosQuery = GastosOperativos::whereDate('fecha', '>=', $startDate->toDateString())
            ->whereDate('fecha', '<=', $endDate->toDateString());
        if (!empty($filters['entidad_id'])) {
            $gastosQuery->where('entidad_id', $filters['entidad_id']);
        }
        $totalGastos = $gastosQuery->sum('monto');

        // Calcular ganancia total del negocio
        $ingresosTotales = $totalIngresosServicios + $totalIngresosProductos + $totalIngresosAdicionales;
        $gananciaTotal = $ingresosTotales - $totalPagosOperadores - $totalGastos;

        // Calcular totales por método de pago
        $totalEfectivo = 0;
        $totalTransferencia = 0;

        foreach ($operadores as $operador) {
            foreach ($operador->serviciosRealizados as $servicio) {
                if ($servicio->monto_efectivo > 0) {
                    $totalEfectivo += $servicio->monto_efectivo;
                }
                if ($servicio->monto_transferencia > 0) {
                    $totalTransferencia += $servicio->monto_transferencia;
                }
            }
        }

        return [
            'title' => 'Reporte Completo de Ganancias',
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'columns' => [
                ['key' => 'operador', 'header' => 'Operador'],
                ['key' => 'entidad', 'header' => 'Entidad'],
                ['key' => 'ingresos_servicios', 'header' => 'Ingresos por Servicios ($)'],
                ['key' => 'pagos_servicios', 'header' => 'Pagos por Servicios ($)'],
                ['key' => 'pagos_realizados', 'header' => 'Pagos Realizados ($)'],
                ['key' => 'pagos_pendientes', 'header' => 'Pagos Pendientes ($)'],
                ['key' => 'ganancia_neta_operador', 'header' => 'Ganancia Neta Operador ($)'],
                ['key' => 'cantidad_servicios', 'header' => 'Cantidad Servicios'],
                ['key' => 'cantidad_pagos', 'header' => 'Cantidad Pagos']
            ],
            'data' => $data,
            'summary' => [
                'total_operadores' => count($data),
                'ingresos_servicios' => $totalIngresosServicios,
                'ingresos_productos' => $totalIngresosProductos,
                'ingresos_adicionales' => $totalIngresosAdicionales,
                'ingresos_totales' => $ingresosTotales,
                'pagos_operadores' => $totalPagosOperadores,
                'gastos_operativos' => $totalGastos,
                'ganancia_total_negocio' => $gananciaTotal,
                'porcentaje_ganancia' => $ingresosTotales > 0 ? ($gananciaTotal / $ingresosTotales) * 100 : 0,
                'total_efectivo' => $totalEfectivo,
                'total_transferencia' => $totalTransferencia
            ]
        ];
    }

    public function exportReport(string $reportType, ?string $startDate = null, ?string $endDate = null, array $filters = [], string $format = 'excel'): array
    {
        $reportData = $this->generateReport($reportType, $startDate, $endDate, $filters);
        
        if ($format === 'csv') {
            return $this->generateCsvData($reportData);
        } else {
            return $this->generateExcelData($reportData);
        }
    }

    private function generateCsvData(array $reportData): array
    {
        $csvContent = [];
        
        // Título del reporte
        $csvContent[] = [$reportData['title']];
        $csvContent[] = ['Período: ' . $reportData['period']];
        $csvContent[] = []; // Línea vacía
        
        // Encabezados
        $headers = array_map(function($col) {
            return $col['header'];
        }, $reportData['columns']);
        $csvContent[] = $headers;
        
        // Datos
        foreach ($reportData['data'] as $row) {
            $csvRow = [];
            foreach ($reportData['columns'] as $column) {
                $value = $row->{$column['key']};
                
                // Formatear valores numéricos
                if (is_numeric($value) && str_contains($column['header'], '$')) {
                    $value = number_format($value, 2);
                }
                
                $csvRow[] = $value;
            }
            $csvContent[] = $csvRow;
        }
        
        return [
            'format' => 'csv',
            'filename' => $reportData['title'] . '_' . date('Y-m-d_H-i-s') . '.csv',
            'content' => $csvContent,
            'mime_type' => 'text/csv'
        ];
    }

    private function generateExcelData(array $reportData): array
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título del reporte
        $sheet->setCellValue('A1', $reportData['title']);
        $sheet->mergeCells('A1:' . $this->getColumnLetter(count($reportData['columns'])) . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        
        // Período
        $sheet->setCellValue('A2', 'Período: ' . $reportData['period']);
        $sheet->mergeCells('A2:' . $this->getColumnLetter(count($reportData['columns'])) . '2');
        
        // Encabezados
        $headerRow = 4;
        foreach ($reportData['columns'] as $index => $column) {
            $colLetter = $this->getColumnLetter($index + 1);
            $sheet->setCellValue($colLetter . $headerRow, $column['header']);
            $sheet->getStyle($colLetter . $headerRow)->getFont()->setBold(true);
        }
        
        // Datos
        $dataRow = $headerRow + 1;
        foreach ($reportData['data'] as $row) {
            foreach ($reportData['columns'] as $index => $column) {
                $colLetter = $this->getColumnLetter($index + 1);
                $value = $row->{$column['key']};
                
                // Formatear valores numéricos
                if (is_numeric($value) && str_contains($column['header'], '$')) {
                    $value = number_format($value, 2);
                }
                
                $sheet->setCellValue($colLetter . $dataRow, $value);
            }
            $dataRow++;
        }
        
        // Autoajustar columnas
        foreach (range('A', $this->getColumnLetter(count($reportData['columns']))) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Generar archivo en memoria
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $excelContent = ob_get_clean();
        
        return [
            'format' => 'excel',
            'filename' => $reportData['title'] . '_' . date('Y-m-d_H-i-s') . '.xlsx',
            'content' => base64_encode($excelContent),
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
    }
    
    private function getColumnLetter(int $columnNumber): string
    {
        $letter = '';
        while ($columnNumber > 0) {
            $columnNumber--;
            $letter = chr(65 + ($columnNumber % 26)) . $letter;
            $columnNumber = intval($columnNumber / 26);
        }
        return $letter;
    }
} 