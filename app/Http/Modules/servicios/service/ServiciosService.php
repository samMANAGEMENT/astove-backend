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
        return Servicios::get();
    }

    public function modificarServicio(array $data, int $id)
    {
        return Servicios::where('id', $id)->update($data);
    }

    public function servicioRealizado(array $data)
    {
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

        // Trae solo los servicios realizados del mes y año actual con la relación del servicio (para el precio)
        $servicios = ServiciosRealizados::with('servicio', 'empleado')
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->get();

        // Agrupa y suma por empleado
        $pagos = $servicios->groupBy('empleado_id')->map(function ($items, $empleado_id) {
            $empleado = $items->first()->empleado;
            $total = $items->reduce(function ($carry, $item) {
                return $carry + ($item->cantidad * ($item->servicio->precio ?? 0));
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

        // Suma el total: cantidad * precio de cada servicio
        $total = $servicios->reduce(function ($carry, $item) {
            return $carry + ($item->cantidad * ($item->servicio->precio ?? 0));
        }, 0);

        return $total;
    }
}
