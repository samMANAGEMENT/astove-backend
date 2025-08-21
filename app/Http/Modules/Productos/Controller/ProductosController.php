<?php

namespace App\Http\Modules\Productos\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Productos\Request\CrearProductoRequest;
use App\Http\Modules\Productos\Service\ProductosService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductosController extends Controller
{
    public function __construct(private ProductosService $productosService)
    {
    }

    public function crearProducto(CrearProductoRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Si el usuario es admin y proporcionó entidad_id, usarla; si no, usar la del usuario
            if (auth()->user()->esAdmin() && isset($data['entidad_id'])) {
                $data['entidad_id'] = $data['entidad_id'];
            } else {
                $data['entidad_id'] = auth()->user()->obtenerEntidadId();
            }
            
            $producto = $this->productosService->crearProducto($data);
            return response()->json([
                'message' => 'Producto creado exitosamente',
                'data' => $producto
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al crear el producto',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function listarProductos(Request $request): JsonResponse
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $categoriaId = $request->get('categoria_id');
            
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();

            $resultado = $this->productosService->listarProductos($page, $perPage, $search, $categoriaId, $entidadId);
            
            return response()->json($resultado, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al listar productos',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerProducto($id): JsonResponse
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $producto = $this->productosService->obtenerProducto($id, $entidadId);
            return response()->json($producto, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Producto no encontrado',
                'error' => $th->getMessage()
            ], 404);
        }
    }

    public function actualizarProducto(Request $request, $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'nombre' => 'sometimes|string|max:255',
                'categoria_id' => 'sometimes|exists:categorias,id',
                'precio_unitario' => 'sometimes|numeric|min:0',
                'costo_unitario' => 'sometimes|numeric|min:0',
                'stock' => 'sometimes|integer|min:0'
            ]);

            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $producto = $this->productosService->actualizarProducto($data, $id, $entidadId);
            
            return response()->json([
                'message' => 'Producto actualizado exitosamente',
                'data' => $producto
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar el producto',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function eliminarProducto($id): JsonResponse
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $this->productosService->eliminarProducto($id, $entidadId);
            return response()->json([
                'message' => 'Producto eliminado exitosamente'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al eliminar el producto',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerEstadisticas(): JsonResponse
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $estadisticas = $this->productosService->obtenerEstadisticas($entidadId);
            return response()->json($estadisticas, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener estadísticas',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function actualizarStock(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'cantidad' => 'required|integer'
            ]);

            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $producto = $this->productosService->actualizarStock($id, $request->cantidad, $entidadId);
            
            return response()->json([
                'message' => 'Stock actualizado exitosamente',
                'data' => $producto
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar el stock',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
