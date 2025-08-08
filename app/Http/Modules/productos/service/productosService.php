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

    public function listarProductos($page = 1, $perPage = 10, $search = '', $categoriaId = null)
    {
        $query = Productos::with('categoria');

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

    public function obtenerProducto($id)
    {
        return Productos::with('categoria')->findOrFail($id);
    }

    public function actualizarProducto(array $data, $id)
    {
        $producto = Productos::findOrFail($id);
        $producto->update($data);
        return $producto->fresh();
    }

    public function eliminarProducto($id)
    {
        $producto = Productos::findOrFail($id);
        return $producto->delete();
    }

    public function obtenerEstadisticas()
    {
        $totalProductos = Productos::count();
        $totalStock = Productos::sum('stock');
        $valorTotalInventario = Productos::sum(DB::raw('stock * costo_unitario'));
        $gananciaTotalPotencial = Productos::sum(DB::raw('stock * (costo_unitario - precio_unitario)'));
        
        $productosBajoStock = Productos::where('stock', '<=', 5)->count();
        $productosStockOptimo = Productos::where('stock', '>', 10)->count();

        return [
            'total_productos' => $totalProductos,
            'total_stock' => $totalStock,
            'valor_total_inventario' => $valorTotalInventario,
            'ganancia_total_potencial' => $gananciaTotalPotencial,
            'productos_bajo_stock' => $productosBajoStock,
            'productos_stock_optimo' => $productosStockOptimo
        ];
    }

    public function actualizarStock($id, $cantidad)
    {
        $producto = Productos::findOrFail($id);
        $producto->stock += $cantidad;
        $producto->save();
        return $producto;
    }
}
