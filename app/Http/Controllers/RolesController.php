<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permiso;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{
    /**
     * Obtener todos los roles con sus permisos
     */
    public function index()
    {
        $roles = Role::with('permisos')->get();
        
        return response()->json($roles);
    }

    /**
     * Obtener todos los permisos
     */
    public function getPermisos()
    {
        $permisos = Permiso::all();
        
        return response()->json($permisos);
    }

    /**
     * Obtener todos los usuarios con sus roles
     */
    public function getUsers()
    {
        $users = User::with(['role', 'operador.entidades'])->get();
        
        return response()->json($users);
    }

    /**
     * Actualizar el rol de un usuario
     */
    public function updateUserRole(Request $request, $userId)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::findOrFail($userId);
        $user->update(['role_id' => $request->role_id]);

        // Cargar las relaciones para la respuesta
        $user->load(['role', 'operador.entidades']);

        return response()->json([
            'message' => 'Rol actualizado correctamente',
            'user' => $user
        ]);
    }

    /**
     * Crear un nuevo rol
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:roles,nombre',
            'descripcion' => 'nullable|string',
            'permisos' => 'array'
        ]);

        DB::beginTransaction();
        
        try {
            $role = Role::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'estado' => true
            ]);

            if ($request->has('permisos')) {
                $role->permisos()->attach($request->permisos);
            }

            DB::commit();

            $role->load('permisos');

            return response()->json([
                'message' => 'Rol creado correctamente',
                'role' => $role
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error al crear el rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un rol
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|unique:roles,nombre,' . $id,
            'descripcion' => 'nullable|string',
            'estado' => 'boolean',
            'permisos' => 'array'
        ]);

        DB::beginTransaction();
        
        try {
            $role->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado ?? $role->estado
            ]);

            if ($request->has('permisos')) {
                $role->permisos()->sync($request->permisos);
            }

            DB::commit();

            $role->load('permisos');

            return response()->json([
                'message' => 'Rol actualizado correctamente',
                'role' => $role
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error al actualizar el rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un rol
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Verificar si hay usuarios usando este rol
        $usersWithRole = User::where('role_id', $id)->count();
        
        if ($usersWithRole > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el rol porque hay usuarios asignados a él',
                'users_count' => $usersWithRole
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            // Eliminar permisos asociados
            $role->permisos()->detach();
            
            // Eliminar el rol
            $role->delete();

            DB::commit();

            return response()->json([
                'message' => 'Rol eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error al eliminar el rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de roles
     */
    public function getStats()
    {
        $stats = [
            'total_roles' => Role::count(),
            'roles_activos' => Role::where('estado', true)->count(),
            'total_permisos' => Permiso::count(),
            'total_usuarios' => User::count(),
            'usuarios_sin_rol' => User::whereNull('role_id')->count(),
            'usuarios_por_rol' => Role::withCount('users')->get()
        ];

        return response()->json($stats);
    }
} 