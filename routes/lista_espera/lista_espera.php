<?php

use App\Http\Modules\ListaEspera\controller\ListaEsperaController;
use Illuminate\Support\Facades\Route;

Route::prefix('lista-espera')->group(function () {
    Route::controller(ListaEsperaController::class)->group(function () {
        // Rutas para lista de espera
        Route::post('crear-persona', 'crearPersona')->middleware(['permission:crear_lista_espera']);
        Route::get('listar-personas', 'listarPersonas')->middleware(['permission:ver_lista_espera']);
        Route::get('obtener-persona/{id}', 'obtenerPersona')->middleware(['permission:ver_lista_espera']);
        Route::put('modificar-persona/{id}', 'modificarPersona')->middleware(['permission:editar_lista_espera']);
        Route::delete('eliminar-persona/{id}', 'eliminarPersona')->middleware(['permission:eliminar_lista_espera']);
        Route::get('personas-por-fecha/{fecha}', 'obtenerPersonasPorFecha')->middleware(['permission:ver_lista_espera']);
    });
});