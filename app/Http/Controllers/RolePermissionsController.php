<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class RolePermissionsController extends Controller
{
    /**
     * Obtener permisos del rol del usuario autenticado
     */
    public function getUserPermissions(): JsonResponse
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            $role = $user->role;
            
            // Si el usuario no tiene rol, devolver array vacío en lugar de error
            if (!$role) {
                return response()->json([]);
            }

            // Obtener permisos del rol
            $permissions = $role->permisos()
                ->where('estado', true)
                ->get()
                ->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'nombre' => $permission->nombre,
                        'descripcion' => $permission->descripcion,
                        'modulo' => $permission->modulo,
                        'estado' => $permission->estado
                    ];
                });

            return response()->json($permissions);

        } catch (\Exception $e) {
            \Log::error('Error en getUserPermissions: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener permisos',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
