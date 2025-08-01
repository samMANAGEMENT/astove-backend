<?php

use Illuminate\Support\Facades\Route;
use App\http\modules\pagos\controller\pagosController;

Route::prefix('pagos')->group(function () {
	Route::controller(pagosController::class)->group(function () {
		Route::post('crear-pago', 'crearPago');
		Route::get('listar-pagos', 'listarPago');
		Route::get('empleados-completo', 'getPagosEmpleadosCompleto');
		Route::get('ganancia-neta', 'getGananciaNeta');
		Route::post('crear-pago-semanal', 'crearPagoSemanal');
		Route::get('servicios-pendientes/{empleadoId}', 'getServiciosPendientesEmpleado');
		Route::get('estado-empleados', 'getEstadoPagosEmpleados');
	});
});