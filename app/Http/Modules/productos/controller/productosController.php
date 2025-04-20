<?php

namespace App\http\modules\productos\controller;

use App\Http\Controllers\Controller;
use App\http\modules\productos\service\productosService;
use Illuminate\Http\Request;

class productosController extends Controller
{
    public function __construct(private productosService $productosService)
    {
    }

    public function crearProducto(Request $data){
        try {
            $crearProducto = $this->productosService->crearProducto($data->all());
            return response()->json($crearProducto, 200);
        } catch (\Throwable $th) {
            return response()->json('error', 500);
        }
    }

    public function listarProducto(){
        try {
            $crearProducto = $this->productosService->listarProducto();
            return response()->json($crearProducto, 200);
        } catch (\Throwable $th) {
            return response()->json('error', 500);
        }
    }
}
