<?php

namespace App\Http\Modules\Auth\controller;

use App\Http\Modules\Auth\Request\crearUsuarioRequest;
use App\Http\Modules\Auth\Request\loguearUsuarioRequest;
use App\Http\Modules\Auth\service\AuthService;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController
{

    public function __construct(private AuthService $authService) {}


    public function register(crearUsuarioRequest $request)
    {
        try {
            $usuario = $this->authService->crearUsuario($request->validated());
            return response()->json($usuario, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function login(loguearUsuarioRequest $request)
    {
        try {
            $login = $this->authService->login($request->validated());
            return response()->json($login, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()],500);
        }
    }


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }
}
