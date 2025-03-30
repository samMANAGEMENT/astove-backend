<?php

use App\Http\Modules\Operadores\controller\operadoresController;
use Illuminate\Support\Facades\Route;

Route::prefix('operadores')->group(function () {
	Route::controller(operadoresController::class)->group(function () {
		Route::get('listar-operador', 'listarOperadores');
	});
});