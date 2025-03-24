<?php

use App\Http\Modules\Auth\controller\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Rutas de Sanctum protegidas

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::post('/crear-entidad', 'App\modules\entidades\controller\entidadesController@crearEntidad');

Route::get('/listar-entidades', 'App\modules\entidades\controller\entidadesController@listarEntidades');

Route::post('/crear-empleado', 'App\modules\empleados\controller\empleadosController@crearEmpleado');

Route::get('/listar-empleados', 'App\modules\empleados\controller\empleadosController@listarEmpleados');
