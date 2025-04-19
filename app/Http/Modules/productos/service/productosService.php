<?php

namespace App\http\modules\productos\service;

use App\Http\Modules\productos\models\productos;

class productosService
{
    public function crearProducto($data)
    {
        return productos::create($data);
    }

    public function listarProducto($data)
    {
        return productos::get();
    }
}
