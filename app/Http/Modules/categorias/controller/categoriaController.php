<?php

namespace App\Http\Modules\categorias\controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Modules\categorias\service\categoriaService;
use Illuminate\Http\JsonResponse;


class categoriaController extends Controller
{
    public function __construct(private CategoriaService $categoriasService)
    {}
    public function inserCategorias(Request $categorias)
    {
        try {
            
            $createCategoria = $this->categoriasService->createCategoria($categorias->all());
            return response()->json($createCategoria);
        } catch (\Throwable $th) {
            return response()->json($th, );
        }
    }
    public function listCategoria(){
        try {
            $infoCategoria = $this->categoriasService->listCategoria();
            return response()->json($infoCategoria);
        } catch (\Throwable $th) {
            return response()->json($th, );
            
        }
    }

    public function updateCategoria(Request $request, $id): JsonResponse
    {
        try {
            $updateCategoria = $this->categoriasService->updateCategoria($id, $request->all());
            return response()->json(data: $updateCategoria);
        } catch (\Throwable $th) {
            return response()->json(data: $th);
        }
    }

    
}