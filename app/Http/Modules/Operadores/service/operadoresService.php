<?php

namespace App\Http\Modules\Operadores\service;

use App\Http\Modules\Operadores\models\Operadores;
use Illuminate\Http\Request;

class operadoresService
{
    public function listarOperadores()
    {
        return Operadores::get();
    }
}
