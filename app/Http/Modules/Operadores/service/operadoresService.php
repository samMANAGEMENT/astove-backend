<?php

namespace App\Http\Modules\Operadores\service;

use App\Http\Modules\Operadores\Models\Operadores;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class operadoresService
{

    public function crearOperador($data)
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

            $usuario = User::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'operador_id' => $operador->id,
            ]);

            DB::commit();

            return $operador->load(['entidades', 'cargo', 'usuario']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Error al crear el operador: " . $e->getMessage(), 500, $e);
        }
    }

    public function listarOperadores($entidadId = null)
    {
        $query = Operadores::with(['entidades', 'cargo', 'usuario']);
        
        // Filtrar por entidad si se proporciona
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        return $query->get();
    }

    public function modificarOperador($id, $data)
    {
        try {
            DB::beginTransaction();

            $operador = Operadores::findOrFail($id);
            
            // Verificar que el usuario tenga permisos para modificar este operador
            $userEntidadId = auth()->user()->obtenerEntidadId();
            if ($userEntidadId && $operador->entidad_id !== $userEntidadId) {
                throw new \Exception('No tienes permisos para modificar este operador');
            }

            $operador->update([
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'entidad_id' => $data['entidad_id'],
                'telefono' => $data['telefono'],
                'cargo_id' => $data['cargo_id']
            ]);

            DB::commit();

            return $operador->load(['entidades', 'cargo', 'usuario']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Error al modificar el operador: " . $e->getMessage(), 500, $e);
        }
    }
}
