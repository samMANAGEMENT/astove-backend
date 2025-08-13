<?php

namespace App\Http\Modules\Gastos\service;

use App\Http\Modules\Gastos\models\GastosOperativos;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GastosService
{
    public function crearGasto(array $data)
    {
        $user = Auth::user();
        $entidadId = $user->obtenerEntidadId();

        // Asegurar que la fecha tenga la hora correcta en la zona horaria local
        if (isset($data['fecha']) && !str_contains($data['fecha'], ' ')) {
            // Si solo se envía la fecha (YYYY-MM-DD), agregar la hora actual
            $data['fecha'] = $data['fecha'] . ' ' . date('H:i:s');
        }

        return GastosOperativos::create([
            'entidad_id' => $entidadId,
            'descripcion' => $data['descripcion'],
            'monto' => $data['monto'],
            'fecha' => $data['fecha']
        ]);
    }

    public function listarGastos($page = 1, $perPage = 10, $search = '')
    {
        $user = Auth::user();
        $entidadId = $user->obtenerEntidadId();

        $query = GastosOperativos::where('entidad_id', $entidadId)
            ->with('entidad');

        if (!empty($search)) {
            $query->where('descripcion', 'like', '%' . $search . '%');
        }

        return $query->orderBy('fecha', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function obtenerGasto($id)
    {
        $user = Auth::user();
        $entidadId = $user->obtenerEntidadId();

        return GastosOperativos::where('entidad_id', $entidadId)
            ->where('id', $id)
            ->with('entidad')
            ->first();
    }

    public function actualizarGasto(array $data, $id)
    {
        $user = Auth::user();
        $entidadId = $user->obtenerEntidadId();

        $gasto = GastosOperativos::where('entidad_id', $entidadId)
            ->where('id', $id)
            ->first();

        if (!$gasto) {
            throw new \Exception('Gasto no encontrado');
        }

        // Asegurar que la fecha tenga la hora correcta en la zona horaria local
        if (isset($data['fecha']) && !str_contains($data['fecha'], ' ')) {
            // Si solo se envía la fecha (YYYY-MM-DD), agregar la hora actual
            $data['fecha'] = $data['fecha'] . ' ' . date('H:i:s');
        }

        $gasto->update([
            'descripcion' => $data['descripcion'],
            'monto' => $data['monto'],
            'fecha' => $data['fecha']
        ]);

        return $gasto;
    }

    public function eliminarGasto($id)
    {
        $user = Auth::user();
        $entidadId = $user->obtenerEntidadId();

        $gasto = GastosOperativos::where('entidad_id', $entidadId)
            ->where('id', $id)
            ->first();

        if (!$gasto) {
            throw new \Exception('Gasto no encontrado');
        }

        return $gasto->delete();
    }

    public function obtenerEstadisticas()
    {
        $user = Auth::user();
        $entidadId = $user->obtenerEntidadId();

        $mesActual = Carbon::now()->month;
        $anioActual = Carbon::now()->year;

        $totalGastosMes = GastosOperativos::where('entidad_id', $entidadId)
            ->whereMonth('fecha', $mesActual)
            ->whereYear('fecha', $anioActual)
            ->sum('monto');

        $totalGastosAnio = GastosOperativos::where('entidad_id', $entidadId)
            ->whereYear('fecha', $anioActual)
            ->sum('monto');

        $gastosRecientes = GastosOperativos::where('entidad_id', $entidadId)
            ->orderBy('fecha', 'desc')
            ->limit(5)
            ->get();

        return [
            'total_gastos_mes' => $totalGastosMes,
            'total_gastos_anio' => $totalGastosAnio,
            'gastos_recientes' => $gastosRecientes,
            'mes' => $mesActual,
            'anio' => $anioActual
        ];
    }

    public function totalGastosMes()
    {
        $user = Auth::user();
        $entidadId = $user->obtenerEntidadId();

        $mesActual = Carbon::now()->month;
        $anioActual = Carbon::now()->year;

        return GastosOperativos::where('entidad_id', $entidadId)
            ->whereMonth('fecha', $mesActual)
            ->whereYear('fecha', $anioActual)
            ->sum('monto');
    }
}
