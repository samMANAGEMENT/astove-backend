<?php

use Illuminate\Support\Facades\Route;
use App\http\modules\pagos\controller\pagosController;

Route::prefix('pagos')->group(function () {
	Route::controller(pagosController::class)->group(function () {
		Route::post('crear-pago', 'crearPago');
		Route::get('listar-pagos', 'listarPago');
	});
});