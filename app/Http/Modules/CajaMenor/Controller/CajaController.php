<?php

namespace App\Http\Modules\CajaMenor\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Modules\CajaMenor\Service\CajaMenorService;
use App\Http\Modules\CajaMenor\Service\CajaService;

class CajaController extends Controller
{

    public function __construct(private CajaService $cajaMenorService) {}

    public function listarCajaMenor()
    {
        try {
            $user = auth()->user();
            $entidadId = $user->obtenerEntidadId();
            $response = $this->cajaMenorService->listarCajaMenor($entidadId);
            return response()->json($response, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Error al listar caja menor', 'message' => $th->getMessage()], 500);
        }
    }
}
