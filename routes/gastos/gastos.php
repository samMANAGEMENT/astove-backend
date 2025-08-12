<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\Gastos\controller\GastosController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Rutas para gastos operativos
    Route::prefix('gastos')->group(function () {
        Route::post('/crear', [GastosController::class, 'crearGasto']);
        Route::get('/listar', [GastosController::class, 'listarGastos']);
        Route::get('/obtener/{id}', [GastosController::class, 'obtenerGasto']);
        Route::put('/actualizar/{id}', [GastosController::class, 'actualizarGasto']);
        Route::delete('/eliminar/{id}', [GastosController::class, 'eliminarGasto']);
        Route::get('/estadisticas', [GastosController::class, 'obtenerEstadisticas']);
        Route::get('/total-mes', [GastosController::class, 'totalGastosMes']);
    });
});
