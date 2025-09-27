<?php

namespace App\Http\Modules\ListaEspera\controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\ListaEspera\service\ListaEsperaService;
use App\Http\Modules\ListaEspera\Request\crearPersonaRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ListaEsperaController extends Controller
{
    protected $listaEsperaService;

    public function __construct(ListaEsperaService $listaEsperaService)
    {
        $this->listaEsperaService = $listaEsperaService;
    }

    /**
     * Crear nueva persona en lista de espera
     */
    public function crearPersona(crearPersonaRequest $request): JsonResponse
    {
        try {
            $persona = $this->listaEsperaService->crearPersona($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Persona agregada a la lista de espera correctamente',
                'data' => $persona
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear persona en lista de espera',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todas las personas en lista de espera
     */
    public function listarPersonas(Request $request): JsonResponse
    {
        try {
            $personas = $this->listaEsperaService->listarPersonas($request->all());
            return response()->json([
                'success' => true,
                'data' => $personas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar personas en lista de espera',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener persona específica
     */
    public function obtenerPersona(int $id): JsonResponse
    {
        try {
            $persona = $this->listaEsperaService->obtenerPersona($id);
            return response()->json([
                'success' => true,
                'data' => $persona
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener persona',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modificar persona en lista de espera
     */
    public function modificarPersona(crearPersonaRequest $request, int $id): JsonResponse
    {
        try {
            $persona = $this->listaEsperaService->modificarPersona($id, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Persona actualizada correctamente',
                'data' => $persona
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar persona',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar persona de lista de espera
     */
    public function eliminarPersona(int $id): JsonResponse
    {
        try {
            $this->listaEsperaService->eliminarPersona($id);
            return response()->json([
                'success' => true,
                'message' => 'Persona eliminada de la lista de espera correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar persona',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener personas por fecha específica
     */
    public function obtenerPersonasPorFecha(string $fecha): JsonResponse
    {
        try {
            $personas = $this->listaEsperaService->obtenerPersonasPorFecha($fecha);
            return response()->json([
                'success' => true,
                'data' => $personas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener personas por fecha',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
