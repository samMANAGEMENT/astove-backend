<?php

use Illuminate\Support\Facades\Route;
use App\http\modules\pagos\controller\pagosController;

Route::prefix('pagos')->group(function () {
	Route::controller(pagosController::class)->group(function () {
		Route::post('crear-pago', 'crearPago')->middleware(['permission:crear_pagos']);
		Route::get('listar-pagos', 'listarPago')->middleware(['entity.access', 'permission:ver_pagos']);
		Route::get('empleados-completo', 'getPagosEmpleadosCompleto')->middleware(['permission:ver_pagos']);
		Route::get('ganancia-neta', 'getGananciaNeta')->middleware(['permission:ver_pagos']);
		Route::post('crear-pago-semanal', 'crearPagoSemanal')->middleware(['permission:crear_pagos']);
		Route::get('servicios-pendientes/{empleadoId}', 'getServiciosPendientesEmpleado')->middleware(['permission:ver_pagos']);
		Route::get('servicios-empleado/{empleadoId}', 'getServiciosEmpleado')->middleware(['permission:ver_servicios_empleado']);
		Route::get('estado-empleados', 'getEstadoPagosEmpleados')->middleware(['permission:ver_pagos']);
	});
});