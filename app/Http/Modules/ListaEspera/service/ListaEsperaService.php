<?php

namespace App\Http\Modules\ListaEspera\service;

use App\Http\Modules\ListaEspera\Models\ListaEspera;
use Illuminate\Database\Eloquent\Collection;

class ListaEsperaService
{
    /**
     * Crear nueva persona en lista de espera
     */
    public function crearPersona(array $data): ListaEspera
    {
        return ListaEspera::create($data);
    }

    /**
     * Listar todas las personas en lista de espera
     */
    public function listarPersonas(array $filters = []): Collection
    {
        $query = ListaEspera::query();

        // Aplicar filtros si existen
        if (isset($filters['fecha'])) {
            $query->porFecha($filters['fecha']);
        }

        return $query->ordenado()->get();
    }

    /**
     * Obtener persona específica
     */
    public function obtenerPersona(int $id): ListaEspera
    {
        return ListaEspera::findOrFail($id);
    }

    /**
     * Modificar persona en lista de espera
     */
    public function modificarPersona(int $id, array $data): ListaEspera
    {
        $persona = ListaEspera::findOrFail($id);
        $persona->update($data);
        return $persona->fresh();
    }

    /**
     * Eliminar persona de lista de espera
     */
    public function eliminarPersona(int $id): bool
    {
        $persona = ListaEspera::findOrFail($id);
        return $persona->delete();
    }

    /**
     * Obtener personas por fecha específica
     */
    public function obtenerPersonasPorFecha(string $fecha): Collection
    {
        return ListaEspera::porFecha($fecha)->ordenado()->get();
    }
}
