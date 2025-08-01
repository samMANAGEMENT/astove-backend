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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//Rutas de Sanctum protegidas (con login)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    require __DIR__ . '/entidad/entidad.php';
    require __DIR__ . '/operador/operador.php';
    require __DIR__ . '/servicios/servicio.php';    
    require __DIR__ . '/cargo/cargo.php';
    require __DIR__ . '/ventas/ventas.php';
    require __DIR__ . '/pagos/pagos.php';
    require __DIR__ . '/roles/roles.php';
});