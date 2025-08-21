<?php

namespace App\Http\Modules\Productos\Service;

use App\Http\Modules\Productos\Models\Productos;
use Illuminate\Support\Facades\DB;

class ProductosService
{
    public function crearProducto(array $data)
    {
        return Productos::create($data);
    }

    public function listarProductos($page = 1, $perPage = 10, $search = '', $categoriaId = null, $entidadId = null)
    {
        $query = Productos::with('categoria');

        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }

        if (!empty($search)) {
            $query->where('nombre', 'like', '%' . $search . '%');
        }

        if ($categoriaId) {
            $query->where('categoria_id', $categoriaId);
        }

        $total = $query->count();
        $productos = $query->orderBy('created_at', 'desc')
                          ->skip(($page - 1) * $perPage)
                          ->take($perPage)
                          ->get();

        return [
            'data' => $productos,
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

    public function obtenerProducto($id, $entidadId = null)
    {
        $query = Productos::with('categoria')->where('id', $id);
        
        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        return $query->firstOrFail();
    }

    public function actualizarProducto(array $data, $id, $entidadId = null)
    {
        $query = Productos::where('id', $id);
        
        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        $producto = $query->firstOrFail();
        $producto->update($data);
        return $producto->fresh();
    }

    public function eliminarProducto($id, $entidadId = null)
    {
        $query = Productos::where('id', $id);
        
        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        $producto = $query->firstOrFail();
        return $producto->delete();
    }

    public function obtenerEstadisticas($entidadId = null)
    {
        $query = Productos::query();
        
        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        $totalProductos = $query->count();
        $totalStock = $query->sum('stock');
        $valorTotalInventario = $query->sum(DB::raw('stock * costo_unitario'));
        $gananciaTotalPotencial = $query->sum(DB::raw('stock * (precio_unitario - costo_unitario)'));
        
        $productosBajoStock = $query->where('stock', '<=', 5)->count();
        $productosStockOptimo = $query->where('stock', '>', 10)->count();

        return [
            'total_productos' => $totalProductos,
            'total_stock' => $totalStock,
            'valor_total_inventario' => $valorTotalInventario,
            'ganancia_total_potencial' => $gananciaTotalPotencial,
            'productos_bajo_stock' => $productosBajoStock,
            'productos_stock_optimo' => $productosStockOptimo
        ];
    }

    public function actualizarStock($id, $cantidad, $entidadId = null)
    {
        $query = Productos::where('id', $id);
        
        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        $producto = $query->firstOrFail();
        $producto->stock += $cantidad;
        $producto->save();
        return $producto;
    }
}
