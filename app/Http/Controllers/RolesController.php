<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permiso;
use Illuminate\Http\JsonResponse;

class RolesController extends Controller
{
    /**
     * Obtener lista de roles con sus permisos
     */
    public function index(): JsonResponse
    {
        try {
            $roles = Role::with('permisos')
                ->get()
                ->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'nombre' => $role->nombre,
                        'descripcion' => $role->descripcion,
                        'estado' => $role->estado,
                        'permisos' => $role->permisos->map(function ($permiso) {
                            return [
                                'id' => $permiso->id,
                                'nombre' => $permiso->nombre,
                                'descripcion' => $permiso->descripcion,
                                'modulo' => $permiso->modulo,
                                'estado' => $permiso->estado
                            ];
                        })
                    ];
                });

            return response()->json($roles);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener roles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los permisos disponibles
     */
    public function getPermisos(): JsonResponse
    {
        try {
            $permisos = Permiso::where('estado', true)
                ->orderBy('modulo')
                ->orderBy('nombre')
                ->get()
                ->map(function ($permiso) {
                    return [
                        'id' => $permiso->id,
                        'nombre' => $permiso->nombre,
                        'descripcion' => $permiso->descripcion,
                        'modulo' => $permiso->modulo,
                        'estado' => $permiso->estado
                    ];
                });

            return response()->json($permisos);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener permisos',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 