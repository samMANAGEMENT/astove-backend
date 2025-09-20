<?php

use App\Http\Modules\CajaMenor\Controller\CajaController;
use Illuminate\Support\Facades\Route;

Route::prefix('caja-menor')->group(function () {
    Route::controller(CajaController::class)->group(function () {
        Route::get('listar-cajas', 'listarCajaMenor');
    });
});
