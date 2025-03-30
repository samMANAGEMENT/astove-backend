<?php

use App\Http\Modules\Entidades\controller\EntidadesController;
use Illuminate\Support\Facades\Route;

Route::prefix('entidad')->group(function () {
	Route::controller(EntidadesController::class)->group(function () {
		Route::post('crear-entidad', 'crearEntidad');
		Route::get('listar-entidades', 'listarEntidad');
	});
});