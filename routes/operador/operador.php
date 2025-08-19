<?php

use App\Http\Modules\Operadores\controller\operadoresController;
use App\Http\Modules\Auth\controller\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('operadores')->group(function () {
	Route::controller(operadoresController::class, AuthController::class)->group(function () {
		Route::post('crear-operador', 'crearOperador');
		Route::get('listar-operador', 'listarOperadores');
	});
});