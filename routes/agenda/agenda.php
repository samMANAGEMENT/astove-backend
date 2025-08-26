<?php

use App\Http\Modules\Agenda\controller\AgendaController;
use Illuminate\Support\Facades\Route;

Route::prefix('agenda')->group(function () {
    Route::controller(AgendaController::class)->group(function () {
        // Rutas para agendas
        Route::post('crear-agenda', 'crearAgenda')->middleware(['permission:crear_agendas']);
        Route::get('listar-agendas', 'listarAgendas')->middleware(['permission:ver_agendas']);
        Route::get('obtener-agenda/{id}', 'obtenerAgenda')->middleware(['permission:ver_agendas']);
        Route::put('modificar-agenda/{id}', 'modificarAgenda')->middleware(['permission:editar_agendas']);
        Route::delete('eliminar-agenda/{id}', 'eliminarAgenda')->middleware(['permission:eliminar_agendas']);
        
        // Rutas para horarios
        Route::post('crear-horario', 'crearHorario')->middleware(['permission:crear_horarios']);
        Route::put('modificar-horario/{id}', 'modificarHorario')->middleware(['permission:editar_horarios']);
        Route::delete('eliminar-horario/{id}', 'eliminarHorario')->middleware(['permission:eliminar_horarios']);
        Route::get('horarios-agenda/{agendaId}', 'obtenerHorariosPorAgenda')->middleware(['permission:ver_horarios']);
        Route::get('consultar-espacios/{agendaId}', 'consultarEspaciosDisponibles')->middleware(['permission:ver_agendas']);
        
        // Rutas para calendario y citas
        Route::get('calendario/{agendaId}', 'obtenerCalendarioAgenda')->middleware(['permission:ver_agendas']);
        Route::get('disponibilidad-tiempo-real', 'obtenerDisponibilidadTiempoReal')->middleware(['permission:ver_agendas']);
        Route::post('crear-cita', 'crearCita')->middleware(['permission:crear_citas']);
        Route::put('actualizar-cita/{id}', 'actualizarCita')->middleware(['permission:editar_citas']);
        Route::delete('eliminar-cita/{id}', 'eliminarCita')->middleware(['permission:eliminar_citas']);
    });
});
