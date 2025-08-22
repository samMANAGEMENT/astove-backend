<?php

use App\Http\Modules\Inventario\Controller\InventarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('inventario')->group(function () {
    Route::controller(InventarioController::class)->group(function () {
        // Rutas principales
        Route::post('/crear-inventario', 'crearInventario')->middleware('permission:crear_inventario');
        Route::get('/listar-inventario', 'listarInventario')->middleware('permission:ver_inventario');
        Route::get('/obtener-inventario/{id}', 'obtenerInventario')->middleware('permission:ver_inventario');
        Route::put('/actualizar-inventario/{id}', 'actualizarInventario')->middleware('permission:editar_inventario');
        Route::delete('/eliminar-inventario/{id}', 'eliminarInventario')->middleware('permission:eliminar_inventario');
        
        // Rutas de estadísticas
        Route::get('/estadisticas', 'obtenerEstadisticas')->middleware('permission:ver_inventario');
        
        // Rutas de gestión de stock
        Route::put('/actualizar-stock/{id}', 'actualizarStock')->middleware('permission:editar_inventario');
        Route::put('/cambiar-estado/{id}', 'cambiarEstado')->middleware('permission:editar_inventario');
        
        // Rutas de movimientos
        Route::get('/movimientos/{id}', 'obtenerMovimientos')->middleware('permission:ver_inventario');
    });
});
