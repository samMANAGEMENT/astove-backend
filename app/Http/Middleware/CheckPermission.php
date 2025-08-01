<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permiso = null, string $modulo = null): Response
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        // Si es admin, tiene acceso a todo
        if ($user->esAdmin()) {
            return $next($request);
        }

        // Verificar permiso específico
        if ($permiso && !$user->tienePermiso($permiso)) {
            return response()->json(['message' => 'No tienes permiso para acceder a este recurso'], 403);
        }

        // Verificar permiso por módulo
        if ($modulo && !$user->tienePermisoModulo($modulo)) {
            return response()->json(['message' => 'No tienes permiso para acceder a este módulo'], 403);
        }

        return $next($request);
    }
} 