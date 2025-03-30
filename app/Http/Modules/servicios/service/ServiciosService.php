<?php

namespace App\Http\Modules\servicios\service;

use App\Http\Modules\servicios\models\Servicios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiciosService 
{
    public function crearServicio(array $data)
    {
        return Servicios::create($data);
    }

    public function listarServicio()
    {
        return Servicios::get();
    }

    public function modificarServicio(array $data, int $id)
    {
        return Servicios::where('id', $id)->update($data);
    }
}
