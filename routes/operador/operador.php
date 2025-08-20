<?php

use App\Http\Modules\Operadores\controller\operadoresController;
use App\Http\Modules\Auth\controller\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('operadores')->group(function () {
	Route::controller(operadoresController::class)->group(function () {
		Route::post('crear-operador', 'crearOperador')->middleware(['permission:crear_operadores']);
		Route::get('listar-operador', 'listarOperadores')->middleware(['permission:ver_operadores']);
		Route::put('modificar-operador/{id}', 'modificarOperador')->middleware(['permission:editar_operadores']);
	});
});