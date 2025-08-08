<?php

use App\Http\Modules\Productos\Controller\ProductosController;
use Illuminate\Support\Facades\Route;

Route::prefix('productos')->group(function () {
    Route::controller(ProductosController::class)->group(function () {
        Route::post('crear-producto', 'crearProducto');
        Route::get('listar-productos', 'listarProductos');
        Route::get('obtener-producto/{id}', 'obtenerProducto');
        Route::put('actualizar-producto/{id}', 'actualizarProducto');
        Route::delete('eliminar-producto/{id}', 'eliminarProducto');
        Route::get('estadisticas', 'obtenerEstadisticas');
        Route::put('actualizar-stock/{id}', 'actualizarStock');
    });
});
