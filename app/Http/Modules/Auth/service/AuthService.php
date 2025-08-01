<?php

namespace App\Http\Modules\Auth\service;

use App\Http\Modules\Auth\models\Auth;
use App\Http\Modules\Operadores\models\Operadores;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function PHPUnit\Framework\throwException;

class AuthService
{
    public function crearUsuario(array $data)
    {
        try {
            DB::beginTransaction();

            $operador = Operadores::create([
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'entidad_id' => $data['entidad_id'],
                'telefono' => $data['telefono'],
                'cargo_id' => $data['cargo_id']
            ]);

            $usuario = Auth::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'operador_id' => $operador->id,
            ]);

            DB::commit();

            return [$operador, $usuario];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Error al crear el usuario: " . $e->getMessage(), 500, $e);
        }
    }
    
    public function login(array $request)
    {
        // Buscar usuario
        $user = User::where('email', $request['email'])->first();

        // Verificar existencia y contraseña
        if (!$user || !Hash::check($request['password'], $user->password)) {
            throw new \Exception('Credenciales inválidas.', 401);
        }

        // Crear token
        $tokenResult = $user->createToken('auth_token');

        // Obtener solo el token limpio (sin ID adelante)
        $plainTextToken = explode('|', $tokenResult->plainTextToken)[1];

        // Cargar relaciones necesarias
        $user->load(['operador.entidades', 'operador.cargo', 'role']);

        // Responder con información completa del usuario
        return [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role ? [
                    'id' => $user->role->id,
                    'nombre' => $user->role->nombre,
                    'descripcion' => $user->role->descripcion
                ] : null,
                'operador' => $user->operador ? [
                    'id' => $user->operador->id,
                    'nombre' => $user->operador->nombre,
                    'apellido' => $user->operador->apellido,
                    'entidad_id' => $user->operador->entidad_id,
                    'entidad' => $user->operador->entidades ? [
                        'id' => $user->operador->entidades->id,
                        'nombre' => $user->operador->entidades->nombre
                    ] : null,
                    'cargo' => $user->operador->cargo ? [
                        'id' => $user->operador->cargo->id,
                        'nombre' => $user->operador->cargo->nombre
                    ] : null
                ] : null
            ],
            'access_token' => $plainTextToken,
            'token_type' => 'Bearer',
        ];

    }
}
