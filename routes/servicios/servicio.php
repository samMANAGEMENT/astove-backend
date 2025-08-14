<?php

use App\Http\Modules\Entidades\controller\EntidadesController;
use App\Http\Modules\servicios\controller\ServiciosController;
use Illuminate\Support\Facades\Route;

Route::prefix('servicios')->group(function () {
	Route::controller(ServiciosController::class)->group(function () {
		Route::post('crear-servicio', 'crearServicio');
		Route::get('listar-servicio', 'listarServicio');
		Route::post('servicio-realizado', 'servicioRealizado');
		Route::post('servicios-multiples', 'serviciosMultiples');
		Route::get('listar-servicios-realizados', 'listarServiciosRealizados');
		Route::get('total-pagar-operador', 'calcularPagosEmpleados');
		Route::get('total-pagar-operador-completo', 'calcularPagosEmpleadosCompleto');
		Route::get('total-ganado', 'totalGanadoServicios');
		Route::get('ganancia-neta', 'calcularGananciaNeta');
		Route::get('ganancias-por-metodo-pago', 'gananciasPorMetodoPago');
		Route::get('total-ganancias-separadas', 'totalGananciasSeparadas');
        Route::put('modificar-servicio/{id}', 'modificarServicio');
        
        // Rutas para Ingresos Adicionales
        Route::post('crear-ingreso-adicional', 'crearIngresoAdicional');
        Route::get('listar-ingresos-adicionales', 'listarIngresosAdicionales');
        Route::get('total-ingresos-adicionales', 'totalIngresosAdicionales');
        Route::get('estadisticas-completas', 'estadisticasCompletas');
        Route::get('ganancias-diarias', 'gananciasDiarias');
        Route::get('ganancias-por-rango', 'gananciasPorRango');
        Route::delete('eliminar-ingreso-adicional/{id}', 'eliminarIngresoAdicional');
        Route::delete('eliminar-servicio-realizado/{id}', 'eliminarServicioRealizado')->middleware(['permission:eliminar_servicios_realizados']);
	});
});