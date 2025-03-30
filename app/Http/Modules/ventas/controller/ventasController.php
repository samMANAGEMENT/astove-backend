<?php

namespace App\Http\Modules\ventas\controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\ventas\service\ventasService;
use Illuminate\Http\Request;

class ventasController extends Controller
{
    public function __construct(private ventasService $ventasService)
    {}

    public function inserVenta(Request $inventas)
    {
        try {
            $infoVenta = $this->ventasService->ventaRegister($inventas->all());
            return response()->json($infoVenta);
        } catch (\Throwable $th) {
            return response()->json($th);
        }
    }

    public function listarVenta(){
        try {
            $infoList = $this->ventasService->listVentas();
            return response()->json($infoList);
        } catch (\Throwable $th) {
            return response()->json($th);
            
        }

    }
}