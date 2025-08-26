<?php

namespace App\Http\Modules\Inventario\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Inventario\Request\CrearInventarioRequest;
use App\Http\Modules\Inventario\Service\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InventarioController extends Controller
{
    public function __construct(private InventarioService $inventarioService)
    {
    }

    public function crearInventario(CrearInventarioRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Si el usuario es admin y proporcionó entidad_id, usarla; si no, usar la del usuario
            if (auth()->user()->esAdmin() && isset($data['entidad_id'])) {
                $data['entidad_id'] = $data['entidad_id'];
            } else {
                $data['entidad_id'] = auth()->user()->obtenerEntidadId();
            }
            
            // Asignar el usuario que crea el registro
            $data['creado_por'] = auth()->id();
            
            $inventario = $this->inventarioService->crearInventario($data);
            return response()->json([
                'message' => 'Artículo de inventario creado exitosamente',
                'data' => $inventario
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al crear el artículo de inventario',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function listarInventario(Request $request): JsonResponse
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();

            $resultado = $this->inventarioService->listarInventario($entidadId, $page, $perPage, $search);
            
            return response()->json($resultado, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al listar inventario',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerInventario($id): JsonResponse
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $inventario = $this->inventarioService->obtenerInventario($id, $entidadId);
            return response()->json($inventario, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Artículo de inventario no encontrado',
                'error' => $th->getMessage()
            ], 404);
        }
    }

    public function actualizarInventario(Request $request, $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'nombre' => 'sometimes|string|max:255',
                'cantidad' => 'sometimes|integer|min:0',
                'costo_unitario' => 'sometimes|numeric|min:0',
                'estado' => 'sometimes|in:activo,inactivo,agotado'
            ]);

            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $inventario = $this->inventarioService->actualizarInventario($data, $id, $entidadId);
            
            return response()->json([
                'message' => 'Artículo de inventario actualizado exitosamente',
                'data' => $inventario
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar el artículo de inventario',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function eliminarInventario($id): JsonResponse
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $this->inventarioService->eliminarInventario($id, $entidadId);
            return response()->json([
                'message' => 'Artículo de inventario eliminado exitosamente'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al eliminar el artículo de inventario',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerEstadisticas(): JsonResponse
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $estadisticas = $this->inventarioService->obtenerEstadisticas($entidadId);
            return response()->json($estadisticas, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener estadísticas',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerMovimientos(Request $request, $id): JsonResponse
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $resultado = $this->inventarioService->obtenerMovimientos($id, $entidadId, $page, $perPage);
            return response()->json($resultado, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener movimientos',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function actualizarStock(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'cantidad' => 'required|integer|min:1',
                'tipo' => 'required|in:agregar,reducir'
            ]);

            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $inventario = $this->inventarioService->actualizarStock(
                $id, 
                $request->cantidad, 
                $request->tipo, 
                $entidadId
            );
            
            return response()->json([
                'message' => 'Stock actualizado exitosamente',
                'data' => $inventario
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar el stock',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function cambiarEstado(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'estado' => 'required|in:activo,inactivo,agotado'
            ]);

            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $inventario = $this->inventarioService->cambiarEstado(
                $id, 
                $request->estado, 
                $entidadId
            );
            
            return response()->json([
                'message' => 'Estado actualizado exitosamente',
                'data' => $inventario
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al cambiar el estado',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // Métodos para manejo de paquetes
    public function actualizarStockPorPaquetes(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'numero_paquetes' => 'required|integer|min:1',
                'tipo' => 'required|in:agregar,reducir'
            ]);

            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $inventario = $this->inventarioService->actualizarStockPorPaquetes(
                $id, 
                $request->numero_paquetes, 
                $request->tipo, 
                $entidadId
            );
            
            return response()->json([
                'message' => 'Stock por paquetes actualizado exitosamente',
                'data' => $inventario
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar el stock por paquetes',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerInformacionPaquetes($id): JsonResponse
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $informacion = $this->inventarioService->obtenerInformacionPaquetes($id, $entidadId);
            return response()->json($informacion, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener información de paquetes',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
