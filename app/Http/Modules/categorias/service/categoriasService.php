<?php

namespace App\http\modules\categorias\service;

use App\Http\Modules\categorias\models\categorias;

class categoriasService
{
    public function crearCategoria($data)
    {
        return categorias::create($data);
    }

    public function listarCategoria()
    {
        return categorias::get();
    }
}
