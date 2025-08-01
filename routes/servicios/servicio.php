<?php

use App\Http\Modules\Entidades\controller\EntidadesController;
use App\Http\Modules\servicios\controller\ServiciosController;
use Illuminate\Support\Facades\Route;

Route::prefix('servicios')->group(function () {
	Route::controller(ServiciosController::class)->group(function () {
		Route::post('crear-servicio', 'crearServicio');
		Route::get('listar-servicio', 'listarServicio');
		Route::post('servicio-realizado', 'servicioRealizado');
		Route::get('listar-servicios-realizados', 'listarServiciosRealizados');
		Route::get('total-pagar-operador', 'calcularPagosEmpleados');
		Route::get('total-pagar-operador-completo', 'calcularPagosEmpleadosCompleto');
		Route::get('total-ganado', 'totalGanadoServicios');
		Route::get('ganancia-neta', 'calcularGananciaNeta');
		Route::get('ganancias-por-metodo-pago', 'gananciasPorMetodoPago');
		Route::get('total-ganancias-separadas', 'totalGananciasSeparadas');
        Route::put('modificar-servicio/{id}', 'modificarServicio');
	});
});