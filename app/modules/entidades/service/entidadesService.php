<?php

namespace App\Modules\entidades\service;

use App\modules\entidades\models\entidades;

class entidadesService
{
    public function crearEntidad($data)
    {

        $existe = entidades::where('nombre', $data['nombre'])->first();

        if ($existe) {
            throw new \Exception('Ya existe una entidad con ese nombre');
        }

        return entidades::create($data);
    }

    public function listarEntidades()
    {
        return entidades::get();
    }

}