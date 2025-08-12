<?php

namespace App\Http\Modules\Gastos\controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Gastos\service\GastosService;
use App\Http\Modules\Gastos\request\crearGastoRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GastosController extends Controller
{
    public function __construct(private GastosService $gastosService)
    {
    }

    public function crearGasto(crearGastoRequest $request): JsonResponse
    {
        try {
            $gasto = $this->gastosService->crearGasto($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Gasto creado exitosamente',
                'data' => $gasto
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el gasto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listarGastos(Request $request): JsonResponse
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');

            $gastos = $this->gastosService->listarGastos($page, $perPage, $search);
            
            return response()->json([
                'success' => true,
                'data' => $gastos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar los gastos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerGasto($id): JsonResponse
    {
        try {
            $gasto = $this->gastosService->obtenerGasto($id);
            
            if (!$gasto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gasto no encontrado'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $gasto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el gasto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualizarGasto(Request $request, $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'descripcion' => 'required|string|max:255',
                'monto' => 'required|numeric|min:0',
                'fecha' => 'required|date'
            ]);

            $gasto = $this->gastosService->actualizarGasto($data, $id);
            
            return response()->json([
                'success' => true,
                'message' => 'Gasto actualizado exitosamente',
                'data' => $gasto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el gasto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function eliminarGasto($id): JsonResponse
    {
        try {
            $this->gastosService->eliminarGasto($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Gasto eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el gasto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerEstadisticas(): JsonResponse
    {
        try {
            $estadisticas = $this->gastosService->obtenerEstadisticas();
            
            return response()->json([
                'success' => true,
                'data' => $estadisticas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function totalGastosMes(): JsonResponse
    {
        try {
            $total = $this->gastosService->totalGastosMes();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_gastos_mes' => $total
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener total de gastos: ' . $e->getMessage()
            ], 500);
        }
    }
}
