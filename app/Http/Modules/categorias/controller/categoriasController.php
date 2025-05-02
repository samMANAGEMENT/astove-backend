<?php

namespace App\http\modules\categorias\controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Categorias\request\crearCategoriaRequest;
use App\http\modules\categorias\service\categoriasService;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;

class categoriasController extends Controller
{
    public function __construct(private categoriasService $categoriasService)
    { 
    }

    public function crearCategoria(crearCategoriaRequest $crearCategoriaRequest){
        try {
            $crearCategoria = $this->categoriasService->crearCategoria($crearCategoriaRequest->validated());
            return response()->json($crearCategoria,201);
        } catch (\Throwable $th) {
            return response()->json('error',500);
        }
    }

    public function listarCategoria(){
        try {
            $crearCategoria = $this->categoriasService->listarCategoria();
            return response()->json($crearCategoria,200);
        } catch (\Throwable $th) {
            return response()->json('error', 500);
        }
    }
} 

