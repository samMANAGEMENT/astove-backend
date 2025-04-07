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
        $updated = categorias::where('id', $id)->update($data);
        if ($updated) {
            return ['message' => 'Categoría actualizada con éxito'];
        } else {
            return ['error' => 'No se encontró la categoría o los datos son iguales'];
        }
    }

    // NUEVAS FUNCIONES (sin tocar las existentes)
    public function softDeleteCategoria($id)
    {
        $categoria = categorias::find($id);
        if($categoria) {
            $categoria->delete();
            return $categoria;
        }
        return ['error' => 'Categoría no encontrada'];
    }

    public function listDeletedCategorias()
    {
        return categorias::onlyTrashed()->get();
    }
}