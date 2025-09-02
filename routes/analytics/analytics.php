<?php

use App\Http\Modules\Analytics\controller\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['entity.access'])->group(function () {
    Route::get('/report-types', [AnalyticsController::class, 'getReportTypes']);
    Route::post('/generate-report', [AnalyticsController::class, 'generateReport']);
    Route::post('/export-report', [AnalyticsController::class, 'exportReport']);
}); 