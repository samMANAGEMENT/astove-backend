<?php

namespace App\Http\Modules\Operadores\service;

use App\Http\Modules\Operadores\models\Operadores;
use Illuminate\Http\Request;

class operadoresService
{

    public function crearOperador($data)
    {
        return Operadores::create($data);
    }

    public function listarOperadores()
    {
        return Operadores::get();
    }
}
