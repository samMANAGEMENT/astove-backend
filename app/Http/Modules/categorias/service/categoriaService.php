<?php

namespace App\Http\Modules\categorias\service;

use App\Http\Modules\categorias\models\categorias;


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

    public function updateCategoria($id, array $data)
    {
        // Buscar y actualizar en una sola línea
        $updated = categorias::where('id', $id)->update($data);

        // Verificar si se actualizó algún registro
        if ($updated) {
            return ['message' => 'Categoría actualizada con éxito'];
        } else {
            return ['error' => 'No se encontró la categoría o los datos son iguales'];
        }
    }
}