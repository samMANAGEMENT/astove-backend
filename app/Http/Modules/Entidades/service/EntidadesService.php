<?php

namespace App\Http\Modules\Entidades\service;

use App\Http\Modules\Entidades\models\Entidades;
use Illuminate\Http\Request;

class EntidadesService
{
    public function crearEntidad(array $data)
    {
        return Entidades::create($data)->get();
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
