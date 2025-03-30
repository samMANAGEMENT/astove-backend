<?php

use App\Http\Modules\ventas\controller\ventasController;
use Illuminate\Support\Facades\Route;

Route::prefix('ventas')->group(function () {
	Route::controller(ventasController::class)->group(function () {
		Route::post('crear-venta', 'inserVenta');
		Route::get('listar-venta', 'listarVenta');
	});
});