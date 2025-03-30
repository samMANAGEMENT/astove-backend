<?php

namespace App\Http\Modules\Cargos\service;

use App\Http\Modules\Cargos\models\Cargos;
use Illuminate\Http\Request;

class cargoService
{
    public function crearCargo(array $data)
    {
        return Cargos::create($data);
    }

    public function listarCargo()
    {
        return Cargos::get();
    }

    public function modificarCargo(array $data, int $id)
    {
        return Cargos::where('id', $id)->update($data);
    }
}
