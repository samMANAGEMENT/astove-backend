<?php

namespace App\Http\Modules\Ventas\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Ventas\Request\CrearVentaRequest;
use App\Http\Modules\Ventas\Service\VentasService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VentasController extends Controller
{
    public function __construct(private VentasService $ventasService)
    {
    }

    public function crearVenta(CrearVentaRequest $request): JsonResponse
    {
        try {
            $empleadoId = auth()->user()->operador->id;
            $venta = $this->ventasService->crearVenta($request->validated(), $empleadoId);
            
            return response()->json([
                'message' => 'Venta registrada exitosamente',
                'data' => $venta
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al registrar la venta',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function listarVentas(Request $request): JsonResponse
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();

            $resultado = $this->ventasService->listarVentas($page, $perPage, $search, $entidadId);
            
            return response()->json($resultado, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al listar ventas',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerVenta($id): JsonResponse
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $venta = $this->ventasService->obtenerVenta($id, $entidadId);
            return response()->json($venta, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Venta no encontrada',
                'error' => $th->getMessage()
            ], 404);
        }
    }

    public function eliminarVenta($id): JsonResponse
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $this->ventasService->eliminarVenta($id, $entidadId);
            return response()->json([
                'message' => 'Venta eliminada exitosamente'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al eliminar la venta',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerEstadisticas(): JsonResponse
    {
        try {
            $user = auth()->user();
            $entidadId = $user->esAdmin() ? null : $user->obtenerEntidadId();
            
            $estadisticas = $this->ventasService->obtenerEstadisticas($entidadId);
            return response()->json($estadisticas, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener estadísticas',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}