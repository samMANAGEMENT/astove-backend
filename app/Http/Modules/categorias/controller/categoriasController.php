<?php

namespace App\http\modules\categorias\controller;

use App\Http\Controllers\Controller;
use App\http\modules\categorias\service\categoriasService;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;

class categoriasController extends Controller
{
    public function __construct(private categoriasService $categoriasService)
    {
        
    }

    public function crearCategoria(Request $data){
        try {
            $crearCategoria = $this->categoriasService->crearCategoria($data->all());
            return response()->json($crearCategoria,200);
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

