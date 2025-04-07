<?php

namespace App\Http\Modules\categorias\controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\categorias\models\categorias;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class categoriaController extends Controller
{
    public function __construct(private \App\Http\Modules\categorias\service\categoriaService $categoriasService)
    {}

    public function inserCategorias(Request $categorias)
    {
        try {
            $createCategoria = $this->categoriasService->createCategoria($categorias->all());
            return response()->json($createCategoria);
        } catch (\Throwable $th) {
            return response()->json($th);
        }
    }

    public function listCategoria()
    {
        try {
            $infoCategoria = $this->categoriasService->listCategoria();
            return response()->json($infoCategoria);
        } catch (\Throwable $th) {
            return response()->json($th);
        }
    }

    public function updateCategoria(Request $request, $id): JsonResponse
    {
        try {
            $updateCategoria = $this->categoriasService->updateCategoria($id, $request->all());
            return response()->json($updateCategoria);
        } catch (\Throwable $th) {
            return response()->json($th);
        }
    }

    public function deleteCategoria($id)
    {
        try {
            $deleted = $this->categoriasService->softDeleteCategoria($id);
            return response()->json($deleted);
        } catch (\Throwable $th) {
            return response()->json($th);
        }
    }

    public function listDeletedCategorias()
    {
        try {
            $deleted = $this->categoriasService->listDeletedCategorias();
            return response()->json($deleted);
        } catch (\Throwable $th) {
            return response()->json($th);
        }
    }

    public function restoreCategoria($id): JsonResponse
    {
        try {
            $categoria = categorias::withTrashed()->find($id);
            
            if (!$categoria) {
                return response()->json(['error' => 'Categoría no encontrada']);
            }

            $categoria->restore();

            return response()->json([
                'success' => true,
                'message' => 'Categoría restaurada con éxito',
                'data' => $categoria
            ]);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
}