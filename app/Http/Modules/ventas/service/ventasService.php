<?php

namespace App\Http\Modules\ventas\service;

use App\Http\Modules\ventas\models\ventas;
use Illuminate\Http\Request;

class ventasService  
{
    public function ventaRegister(array $ventas)
    {
        return ventas::create($ventas); // esta funcion crea una venta en la bd
    }

    public function listVentas()
    {
        return ventas::get();
    }
}
