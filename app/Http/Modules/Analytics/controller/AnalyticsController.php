<?php

namespace App\Http\Modules\Analytics\controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Analytics\service\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $analyticsService) {}

    public function getReportTypes(): JsonResponse
    {
        try {
            $reportTypes = $this->analyticsService->getAvailableReportTypes();
            return response()->json([
                'success' => true,
                'data' => $reportTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de reportes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateReport(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'report_type' => 'required|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ]);

            // Obtener la entidad del usuario autenticado
            $user = auth()->user();
            $userEntityId = null;

            if ($user && $user->operador) {
                $userEntityId = $user->operador->entidad_id;
            }

            // Si es admin, no aplicar filtro de entidad
            $filters = [];
            if (!$user->esAdmin() && $userEntityId) {
                $filters['entidad_id'] = $userEntityId;
            }

            $reportData = $this->analyticsService->generateReport(
                $request->report_type,
                $request->start_date,
                $request->end_date,
                $filters
            );

            return response()->json([
                'success' => true,
                'data' => $reportData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportReport(Request $request)
    {
        try {
            $request->validate([
                'report_type' => 'required|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'format' => 'required|in:excel,csv'
            ]);

            // Obtener la entidad del usuario autenticado
            $user = auth()->user();
            $userEntityId = null;

            if ($user && $user->operador) {
                $userEntityId = $user->operador->entidad_id;
            }

            // Si es admin, no aplicar filtro de entidad
            $filters = [];
            if (!$user->esAdmin() && $userEntityId) {
                $filters['entidad_id'] = $userEntityId;
            }

            $exportData = $this->analyticsService->exportReport(
                $request->report_type,
                $request->start_date,
                $request->end_date,
                $filters,
                $request->format
            );

            if ($exportData['format'] === 'csv') {
                // Para CSV, devolver como respuesta de descarga
                $csvContent = '';
                foreach ($exportData['content'] as $row) {
                    $csvContent .= implode(',', array_map(function($field) {
                        return '"' . str_replace('"', '""', $field) . '"';
                    }, $row)) . "\n";
                }

                return response($csvContent)
                    ->header('Content-Type', $exportData['mime_type'])
                    ->header('Content-Disposition', 'attachment; filename="' . $exportData['filename'] . '"');
            } else {
                // Para Excel, devolver como JSON con contenido base64
                return response()->json([
                    'success' => true,
                    'data' => $exportData
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar reporte: ' . $e->getMessage()
            ], 500);
        }
    }
} 