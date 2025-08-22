<?php

namespace App\Http\Modules\Entidades\service;

use App\Http\Modules\Entidades\models\Entidades;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EntidadesService
{
    public function crearEntidad(array $data)
    {
        return Entidades::create($data);
    }

    public function listarEntidad()
    {
        return Entidades::get();
    }

    public function actualizarEntidad(array $data, $id)
    {
        return Entidades::findOrFail($id)->where('id', $id)->update($data);
    }

    public function modificarEstado(int $id, $data)
    {
        return Entidades::findOrFail($id)->update($data);
    }
}
