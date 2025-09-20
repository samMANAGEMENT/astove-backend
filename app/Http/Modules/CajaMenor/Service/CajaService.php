<?php

namespace App\Http\Modules\CajaMenor\Service;

use App\Http\Modules\CajaMenor\Models\CajaMenor;

class CajaService
{
    public function listarCajaMenor($entidadId)
    {
        if (!$entidadId) {
            throw new \InvalidArgumentException("El ID de la entidad es obligatorio.");
        }

        // Total de los montos
        $totalMontos = CajaMenor::where('entidad_id', $entidadId)->sum('monto');

        // Último registro
        $ultimoRegistro = CajaMenor::with(['entidad', 'servicio'])
            ->where('entidad_id', $entidadId)
            ->latest('created_at')
            ->first();

        return [
            'total' => $totalMontos,
            'ultimo_registro' => $ultimoRegistro,
        ];
    }
}
