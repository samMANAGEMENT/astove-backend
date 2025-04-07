<?php
use App\Http\Modules\categorias\controller\categoriaController;
use Illuminate\Support\Facades\Route;

Route::prefix('categorias')->group(function () {
    Route::controller(categoriaController::class)->group(function () {
        Route::post('crear-categoria', 'inserCategorias');
        Route::get('listar-categoria', 'listCategoria');
        Route::put('actualizar-categoria/{id}', 'updateCategoria');
        // NUEVAS RUTAS
        Route::delete('eliminar-categoria/{id}', 'deleteCategoria');
        Route::get('listar-eliminadas', 'listDeletedCategorias');
		Route::post('restaurar-categoria/{id}', 'restoreCategoria'); // Usa POST como crear/actualizar
    });
});