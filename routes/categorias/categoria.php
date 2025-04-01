<?php

use App\Http\Modules\categorias\controller\categoriaController;
use Illuminate\Support\Facades\Route;

Route::prefix('categorias')->group(function () {
	Route::controller(categoriaController::class)->group(function () {
		Route::post('crear-categoria', 'inserCategorias');
		Route::get('listar-categoria', 'listCategoria');
		Route::put(uri: 'actualizar-categoria/{id}', action: 'updateCategoria');

	});
});