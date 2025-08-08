<?php

namespace App\Http\Modules\Ventas\Service;

use App\Http\Modules\Ventas\Models\Ventas;
use App\Http\Modules\Productos\Models\Productos;
use Illuminate\Support\Facades\DB;

class VentasService
{
    public function crearVenta(array $data, $empleadoId)
    {
        return DB::transaction(function () use ($data, $empleadoId) {
            // Obtener el producto
            $producto = Productos::findOrFail($data['productoId']);
            
            // Verificar stock disponible
            if ($producto->stock < $data['cantidad']) {
                throw new \Exception('Stock insuficiente. Disponible: ' . $producto->stock);
            }
            
            // Calcular totales
            $subtotal = $producto->precio_unitario * $data['cantidad'];
            $gananciaUnitaria = $producto->precio_unitario - $producto->costo_unitario;
            $gananciaTotal = $gananciaUnitaria * $data['cantidad'];
            
            // Crear la venta
            $venta = Ventas::create([
                'total' => $subtotal,
                'ganancia_total' => $gananciaTotal,
                'empleado_id' => $empleadoId,
                'metodo_pago' => $data['metodoPago'],
                'monto_efectivo' => $data['montoEfectivo'] ?? 0,
                'monto_transferencia' => $data['montoTransferencia'] ?? 0,
                'observaciones' => $data['observaciones'] ?? null,
                'fecha' => now()
            ]);
            
            // Asociar producto a la venta
            $venta->productos()->attach($data['productoId'], [
                'cantidad' => $data['cantidad'],
                'subtotal' => $subtotal
            ]);
            
            // Actualizar stock del producto
            $producto->stock -= $data['cantidad'];
            $producto->save();
            
            return $venta->load('productos', 'empleado');
        });
    }

    public function listarVentas($page = 1, $perPage = 10, $search = '')
    {
        $query = Ventas::with(['empleado', 'productos']);

        if (!empty($search)) {
            $query->whereHas('productos', function ($q) use ($search) {
                $q->where('nombre', 'like', '%' . $search . '%');
            })->orWhereHas('empleado', function ($q) use ($search) {
                $q->where('nombre', 'like', '%' . $search . '%')
                  ->orWhere('apellido', 'like', '%' . $search . '%');
            });
        }

        $total = $query->count();
        $ventas = $query->orderBy('created_at', 'desc')
                        ->skip(($page - 1) * $perPage)
                        ->take($perPage)
                        ->get();

        return [
            'data' => $ventas,
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

    public function obtenerVenta($id)
    {
        return Ventas::with(['empleado', 'productos'])->findOrFail($id);
    }

    public function eliminarVenta($id)
    {
        return DB::transaction(function () use ($id) {
            $venta = Ventas::with('productos')->findOrFail($id);
            
            // Restaurar stock de los productos
            foreach ($venta->productos as $producto) {
                $cantidadVendida = $producto->pivot->cantidad;
                $producto->stock += $cantidadVendida;
                $producto->save();
            }
            
            // Eliminar relaciones de productos
            $venta->productos()->detach();
            
            // Eliminar la venta
            $venta->delete();
            
            return true;
        });
    }

    public function obtenerEstadisticas()
    {
        $totalVentas = Ventas::count();
        $totalGanancia = Ventas::sum('ganancia_total');
        $totalVentasHoy = Ventas::whereDate('created_at', today())->count();
        $gananciaHoy = Ventas::whereDate('created_at', today())->sum('ganancia_total');
        
        $ventasPorMetodo = Ventas::select('metodo_pago', DB::raw('count(*) as total'))
            ->groupBy('metodo_pago')
            ->get();

        return [
            'total_ventas' => $totalVentas,
            'total_ganancia' => $totalGanancia,
            'ventas_hoy' => $totalVentasHoy,
            'ganancia_hoy' => $gananciaHoy,
            'ventas_por_metodo' => $ventasPorMetodo
        ];
    }
}
