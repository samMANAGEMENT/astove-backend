<?php

use App\Http\Modules\Entidades\controller\EntidadesController;
use App\Http\Modules\servicios\controller\ServiciosController;
use Illuminate\Support\Facades\Route;

Route::prefix('servicios')->group(function () {
	Route::controller(ServiciosController::class)->group(function () {
		Route::post('crear-servicio', 'crearServicio');
		Route::get('listar-servicio', 'listarServicio');
        Route::put('modificar-servicio', 'modificarServicio');
	});
});