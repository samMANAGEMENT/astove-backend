<?php

namespace App\Http\Modules\Inventario\Service;

use App\Http\Modules\Inventario\Models\Inventario;
use App\Http\Modules\Inventario\Models\InventarioMovimiento;
use Illuminate\Support\Facades\DB;

class InventarioService
{
    public function crearInventario(array $data)
    {
        return Inventario::create($data);
    }

    public function listarInventario($entidadId = null, $page = 1, $perPage = 10, $search = null)
    {
        $query = Inventario::query();
        
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        if ($search) {
            $query->where('nombre', 'like', "%{$search}%");
        }
        
        $total = $query->count();
        
        $inventario = $query->select([
            'id',
            'nombre',
            'cantidad',
            'costo_unitario',
            'estado',
            'tamanio_paquete',
            'created_at'
        ])
        ->orderBy('created_at', 'desc')
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();
        
        return [
            'data' => $inventario,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $total)
            ]
        ];
    }

    public function obtenerInventario($id, $entidadId = null)
    {
        $query = Inventario::with(['entidad', 'creadoPor'])->where('id', $id);
        
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        $inventario = $query->firstOrFail();
        
        // Asegurar que los campos calculados estén disponibles
        $inventario->loadMissing(['entidad', 'creadoPor']);
        
        return $inventario;
    }

    public function actualizarInventario(array $data, $id, $entidadId = null)
    {
        $query = Inventario::where('id', $id);
        
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        $inventario = $query->firstOrFail();
        $inventario->update($data);
        
        // Actualizar estado automáticamente
        $inventario->actualizarEstado();
        
        return $inventario->fresh(['entidad', 'creadoPor']);
    }

    public function eliminarInventario($id, $entidadId = null)
    {
        $query = Inventario::where('id', $id);
        
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        $inventario = $query->firstOrFail();
        return $inventario->delete();
    }

    public function obtenerEstadisticas($entidadId = null)
    {
        $query = Inventario::query();
        
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }

        $totalArticulos = $query->count();
        $totalCantidad = $query->sum('cantidad');
        $valorTotalInventario = $query->sum(DB::raw('cantidad * costo_unitario'));
        $articulosAgotados = $query->where('cantidad', 0)->count();
        $articulosActivos = $query->where('estado', 'activo')->where('cantidad', '>', 0)->count();
        $articulosInactivos = $query->where('estado', 'inactivo')->count();

        return [
            'total_articulos' => $totalArticulos,
            'total_cantidad' => $totalCantidad,
            'valor_total_inventario' => $valorTotalInventario,
            'articulos_agotados' => $articulosAgotados,
            'articulos_activos' => $articulosActivos,
            'articulos_inactivos' => $articulosInactivos
        ];
    }

    public function actualizarStock($id, $cantidad, $tipo = 'agregar', $entidadId = null)
    {
        $query = Inventario::where('id', $id);
        
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        $inventario = $query->firstOrFail();

        if ($tipo === 'agregar') {
            $inventario->agregarStock($cantidad);
        } else {
            $inventario->reducirStock($cantidad);
        }

        return $inventario->fresh(['entidad', 'creadoPor']);
    }

    public function cambiarEstado($id, $estado, $entidadId = null)
    {
        $query = Inventario::where('id', $id);
        
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        $inventario = $query->firstOrFail();
        $inventario->estado = $estado;
        $inventario->save();
        
        return $inventario->fresh(['entidad', 'creadoPor']);
    }

    public function obtenerMovimientos($inventarioId, $entidadId = null, $page = 1, $perPage = 10)
    {
        $query = InventarioMovimiento::with(['usuario'])
            ->whereHas('inventario', function ($q) use ($entidadId) {
                if ($entidadId) {
                    $q->where('entidad_id', $entidadId);
                }
            })
            ->where('inventario_id', $inventarioId);
        
        $total = $query->count();
        
        $movimientos = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();
        
        return [
            'data' => $movimientos,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $total)
            ]
        ];
    }

    // Métodos para manejo de paquetes
    public function actualizarStockPorPaquetes($id, $numeroPaquetes, $tipo = 'agregar', $entidadId = null)
    {
        $query = Inventario::where('id', $id);
        
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        $inventario = $query->firstOrFail();

        if ($tipo === 'agregar') {
            $inventario->agregarPaquetes($numeroPaquetes);
        } else {
            $inventario->reducirPaquetes($numeroPaquetes);
        }

        return $inventario->fresh(['entidad', 'creadoPor']);
    }

    public function obtenerInformacionPaquetes($id, $entidadId = null)
    {
        $query = Inventario::where('id', $id);
        
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        $inventario = $query->firstOrFail();
        
        return [
            'tiene_paquetes' => $inventario->tiene_paquetes,
            'tamanio_paquete' => $inventario->tamanio_paquete,
            'numero_paquetes_disponibles' => $inventario->numero_paquetes_disponibles,
            'cantidad_suelta' => $inventario->cantidad_suelta,
            'costo_por_paquete' => $inventario->costo_por_paquete,
            'informacion_paquetes' => $inventario->informacion_paquetes
        ];
    }
}
