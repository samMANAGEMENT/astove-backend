<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsersController;

Route::prefix('roles')->group(function () {
    Route::controller(RolesController::class)->group(function () {
        // Rutas para obtener datos de roles
        Route::get('/', 'index')->middleware(['permission:ver_roles']);
        Route::get('/permisos', 'getPermisos')->middleware(['permission:ver_roles']);
    });
    
    Route::controller(UsersController::class)->group(function () {
        // Rutas para gestionar usuarios
        Route::get('/usuarios', 'index')->middleware(['permission:ver_roles']);
        Route::put('/usuarios/{userId}/role', 'updateRole')->middleware(['permission:editar_roles']);
    });
}); 