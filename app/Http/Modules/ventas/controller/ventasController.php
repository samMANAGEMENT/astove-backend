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

            $resultado = $this->ventasService->listarVentas($page, $perPage, $search);
            
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
            $venta = $this->ventasService->obtenerVenta($id);
            return response()->json($venta, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Venta no encontrada',
                'error' => $th->getMessage()
            ], 404);
        }
    }

    public function obtenerEstadisticas(): JsonResponse
    {
        try {
            $estadisticas = $this->ventasService->obtenerEstadisticas();
            return response()->json($estadisticas, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener estadísticas',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}