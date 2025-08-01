<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEntityAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        // Si es admin, tiene acceso a todo
        if ($user->esAdmin()) {
            return $next($request);
        }

        // Obtener el ID de la entidad del usuario
        $userEntityId = $user->obtenerEntidadId();

        if (!$userEntityId) {
            return response()->json(['message' => 'Usuario no asociado a ninguna entidad'], 403);
        }

        // Agregar el ID de la entidad a la request para que los controladores lo usen
        $request->merge(['user_entity_id' => $userEntityId]);

        return $next($request);
    }
} 