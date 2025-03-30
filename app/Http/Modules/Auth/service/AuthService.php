<?php

namespace App\Http\Modules\Auth\service;

use App\Http\Modules\Auth\models\Auth;
use App\Http\Modules\Operadores\models\Operadores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            return response()->json(['error' => 'Error al crear el usuario', 'message' => $e->getMessage()], 500);
        }
    }
}
