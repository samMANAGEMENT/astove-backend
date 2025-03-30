<?php

use App\Http\Modules\servicios\controller\ServiciosController;
use Illuminate\Support\Facades\Route;

Route::prefix('cargo')->group(function () {
	Route::controller(ServiciosController::class)->group(function () {
		Route::post('crear-cargp', 'crearCargo');
		Route::get('listar-cargo', 'listarCargo');
        Route::put('modificar-cargo', 'modificarCargo');
	});
});