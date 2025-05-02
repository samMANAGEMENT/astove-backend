<?php

namespace App\Http\Modules\Cargos\controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Cargo\request\crearCargoRequest;
use App\Http\Modules\Cargos\service\cargoService;
use Illuminate\Http\Request;

class cargoController extends Controller
{

    public function __construct(private cargoService $cargoService){}

    public function crearCargo(crearCargoRequest $crearCargoRequest)
    {
        try {
            $cargo = $this->cargoService->crearCargo($crearCargoRequest->validated());
            return response()->json($cargo, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function listarCargo()
    {
        try {
            $cargo = $this->cargoService->listarCargo();
            return response()->json($cargo, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function modificarCargo(Request $data, $id)
    {
        try {
            $cargo = $this->cargoService->modificarCargo($data->all(), $id);
            return response()->json($cargo, 200);
        } catch (\Throwable $th) {
            return response()->json(['Ocurrio un error' => $th->getMessage()], 500);
        }
    }
}
