<?php

namespace App\Http\Modules\Entidades\service;

use App\Http\Modules\Entidades\models\Entidades;
use Illuminate\Http\Request;

class EntidadesService
{
    public function crearEntidad($data)
    {
        return Entidades::create($data)->get();
    }

    public function listarEntidad()
    {
        return Entidades::get();
    }
}
