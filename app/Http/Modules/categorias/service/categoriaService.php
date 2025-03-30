<?php

namespace App\Http\Modules\categorias\service;

use App\Http\Modules\categorias\models\categorias;
use Illuminate\Http\Request;

class categoriaService 
{
    public function createCategoria($categoria)
    {
        return categorias::create($categoria);
    }

    public function listCategoria()
    {
        return categorias::get();
    }


}
