<?php

use App\Http\Modules\Ventas\Controller\VentasController;
use Illuminate\Support\Facades\Route;

Route::prefix('ventas')->group(function () {
    Route::controller(VentasController::class)->group(function () {
        Route::post('crear-venta', 'crearVenta');
        Route::get('listar-ventas', 'listarVentas');
        Route::get('obtener-venta/{id}', 'obtenerVenta');
        Route::delete('eliminar-venta/{id}', 'eliminarVenta');
        Route::get('estadisticas', 'obtenerEstadisticas');
    });
});