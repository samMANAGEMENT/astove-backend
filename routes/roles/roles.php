<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolesController;

Route::prefix('roles')->group(function () {
    Route::controller(RolesController::class)->group(function () {
        // Rutas para obtener datos
        Route::get('/', 'index')->middleware(['permission:ver_roles']);
        Route::get('/permisos', 'getPermisos')->middleware(['permission:ver_roles']);
        Route::get('/usuarios', 'getUsers')->middleware(['permission:ver_roles']);
        Route::get('/stats', 'getStats')->middleware(['permission:ver_roles']);
        
        // Rutas para gestionar roles
        Route::post('/', 'store')->middleware(['permission:crear_roles']);
        Route::put('/{id}', 'update')->middleware(['permission:editar_roles']);
        Route::delete('/{id}', 'destroy')->middleware(['permission:eliminar_roles']);
        
        // Rutas para gestionar usuarios
        Route::put('/usuarios/{userId}/role', 'updateUserRole')->middleware(['permission:editar_roles']);
    });
}); 