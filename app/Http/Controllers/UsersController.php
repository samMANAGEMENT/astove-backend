<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class UsersController extends Controller
{
    /**
     * Obtener lista de usuarios con sus roles
     */
    public function index(): JsonResponse
    {
        try {
            $users = User::with(['role', 'operador.entidades'])
                ->select('id', 'email', 'role_id', 'operador_id')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->operador?->nombre ?? 'Sin nombre',
                        'email' => $user->email,
                        'role' => $user->role ? [
                            'id' => $user->role->id,
                            'nombre' => $user->role->nombre,
                            'descripcion' => $user->role->descripcion
                        ] : null,
                        'entidad' => $user->operador?->entidades?->nombre ?? 'Sin entidad'
                    ];
                });

            return response()->json($users);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener usuarios',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar rol de un usuario
     */
    public function updateRole(Request $request, int $userId): JsonResponse
    {
        try {
            $request->validate([
                'role_id' => 'required|exists:roles,id'
            ]);

            $user = User::find($userId);
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            $user->role_id = $request->role_id;
            $user->save();

            // Retornar usuario actualizado
            $user->load(['role', 'operador.entidades']);
            return response()->json([
                'message' => 'Rol actualizado exitosamente',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->operador?->nombre ?? 'Sin nombre',
                    'email' => $user->email,
                    'role' => $user->role ? [
                        'id' => $user->role->id,
                        'nombre' => $user->role->nombre,
                        'descripcion' => $user->role->descripcion
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar rol',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
