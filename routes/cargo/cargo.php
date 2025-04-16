<?php

use App\Http\Modules\Cargos\controller\cargoController;
use Illuminate\Support\Facades\Route;

Route::prefix('cargo')->group(function () {
	Route::controller(cargoController::class)->group(function () {
		Route::post('crear-cargp', 'crearCargo');
		Route::get('listar-cargo', 'listarCargo');
        Route::put('modificar-cargo', 'modificarCargo');
	});
});